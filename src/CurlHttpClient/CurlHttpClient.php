<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\CurlHttpClient;

use CurlHandle;
use LogicException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\CurlHttpClient\Exception\CurlException;
use MaxMessenger\Bot\HttpClient\Exception\NetworkException;
use MaxMessenger\Bot\HttpClient\HttpResponse;

use function array_replace;
use function count;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_reset;
use function curl_setopt_array;
use function explode;
use function in_array;
use function is_string;
use function strlen;
use function trim;

/**
 * HTTP-клиент на основе curl.
 *
 * Выполняет запросы {@see HttpRequestInterface} и возвращает {@see HttpResponse}. Настройки curl задаются
 * методами `set*`, произвольные параметры — методом {@see setOption()}.
 */
final class CurlHttpClient implements HttpClientInterface
{
    /**
     * Переиспользовать соединение между запросами.
     *
     * Экономит установку соединения и TLS-рукопожатие при частых запросах, например при опросе событий.
     */
    public bool $usePersistentCurlHandle = true;
    private ?CurlHandle $curlHandle = null;

    /**
     * @param array<int, mixed> $options Параметры curl.
     */
    public function __construct(
        private array $options = [],
    ) {}

    /**
     * @return array<int, mixed> Параметры curl.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function request(HttpRequestInterface $request): HttpResponseInterface
    {
        $method = $request->getMethod();
        $headers = $request->getHeaders();
        $body = $request->getBody();

        $options = array_replace($this->options, [
            CURLOPT_URL => $request->getUrl(),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        if ($body !== null) {
            $contentType = $body->getContentType();
            if ($contentType !== null) {
                $headers['Content-Type'] = $contentType;
            }

            $options[CURLOPT_POSTFIELDS] = $body->getBody();
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options[CURLOPT_POSTFIELDS] = '';
        }

        $curlHeaders = [];
        foreach ($headers as $name => $value) {
            $curlHeaders[] = $name . ': ' . $value;
        }

        $options[CURLOPT_HTTPHEADER] = $curlHeaders;

        $timeout = $request->getTimeout();
        if ($timeout !== null) {
            unset($options[CURLOPT_TIMEOUT]);
            $options[CURLOPT_TIMEOUT_MS] = $timeout;
        }

        return $this->execute($options, $request);
    }

    /**
     * Сбрасывает все параметры curl.
     *
     * @return $this
     */
    public function resetOptions(): static
    {
        $this->options = [];

        return $this;
    }

    /**
     * Путь к папке с сертификатами удостоверяющих центров (`CURLOPT_CAPATH`).
     *
     * @param non-empty-string|null $caCertificateDir Путь к папке или `null`, чтобы использовать системные сертификаты.
     * @return $this
     */
    public function setCaCertificateDir(?string $caCertificateDir): static
    {
        return $this->setOption(CURLOPT_CAPATH, $caCertificateDir);
    }

    /**
     * Путь к файлу с сертификатами удостоверяющих центров (`CURLOPT_CAINFO`).
     *
     * @param non-empty-string|null $caCertificatePath Путь к файлу или `null`, чтобы использовать
     *     системные сертификаты.
     * @return $this
     */
    public function setCaCertificatePath(?string $caCertificatePath): static
    {
        return $this->setOption(CURLOPT_CAINFO, $caCertificatePath);
    }

    /**
     * @param non-negative-int|null $connectTimeout Время ожидания соединения в миллисекундах.
     * @return $this
     */
    public function setConnectTimeout(?int $connectTimeout): static
    {
        return $this->setOption(CURLOPT_CONNECTTIMEOUT_MS, $connectTimeout);
    }

    /**
     * Задаёт произвольный параметр curl.
     *
     * @param int $option Константа `CURLOPT_*`.
     * @param string|int|bool|null $value Значение параметра. `null` удаляет параметр.
     * @return $this
     */
    public function setOption(int $option, string|int|bool|null $value): static
    {
        if ($value === null) {
            unset($this->options[$option]);
        } else {
            $this->options[$option] = $value;
        }

        return $this;
    }

    /**
     * @param string $proxyString Адрес прокси-сервера. Пустая строка отключает прокси.
     * @param bool $socks5 Если `true`, используется SOCKS5 вместо HTTP.
     * @return $this
     */
    public function setProxy(string $proxyString = '', bool $socks5 = false): static
    {
        if ($proxyString === '') {
            unset(
                $this->options[CURLOPT_PROXY],
                $this->options[CURLOPT_HTTPPROXYTUNNEL],
                $this->options[CURLOPT_PROXYTYPE],
            );

            return $this;
        }

        $this->options[CURLOPT_PROXY] = $proxyString;
        $this->options[CURLOPT_HTTPPROXYTUNNEL] = true;
        $this->options[CURLOPT_PROXYTYPE] = $socks5 ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP;

        return $this;
    }

    /**
     * @param non-negative-int|null $timeout Максимальное время выполнения запроса в миллисекундах.
     * @return $this
     */
    public function setTimeout(?int $timeout): static
    {
        return $this->setOption(CURLOPT_TIMEOUT_MS, $timeout);
    }

    /**
     * @param non-empty-string|null $userAgent Значение заголовка `User-Agent`.
     * @return $this
     */
    public function setUserAgent(?string $userAgent): static
    {
        return $this->setOption(CURLOPT_USERAGENT, $userAgent);
    }

    /**
     * Проверять ли TLS-сертификат сервера.
     *
     * @param bool $verifySslCertificate `false` отключает проверку сертификата.
     * @return $this
     */
    public function setVerifySslCertificate(bool $verifySslCertificate): static
    {
        return $this->setOption(CURLOPT_SSL_VERIFYPEER, $verifySslCertificate)
            ->setOption(CURLOPT_SSL_VERIFYHOST, $verifySslCertificate ? 2 : 0);
    }

    /**
     * Выполняет запрос.
     *
     * @param array<int, mixed> $options Параметры curl.
     * @param HttpRequestInterface $request Запрос.
     * @return HttpResponseInterface Ответ сервера.
     * @throws NetworkException Если ответ не получен из-за ошибки сети.
     * @throws CurlException При прочих ошибках curl.
     */
    private function execute(array $options, HttpRequestInterface $request): HttpResponseInterface
    {
        /** @var array<string, list<string>> $responseHeaders */
        $responseHeaders = [];
        $options[CURLOPT_HEADERFUNCTION]
            = static function (CurlHandle $curlHandle, string $header) use (&$responseHeaders): int {
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[trim($parts[0])][] = trim($parts[1]);
                }

                return strlen($header);
            };

        $curlHandle = $this->getCurlHandle();

        curl_setopt_array($curlHandle, $options);

        $response = curl_exec($curlHandle);

        if (!is_string($response)) {
            $curlErrno = curl_errno($curlHandle);
            $message = curl_error($curlHandle);

            throw self::isNetworkError($curlErrno)
                ? new NetworkException($message, $curlErrno, $request)
                : new CurlException($message, $curlErrno, $request);
        }

        /** @var string|false|null $contentType */
        $contentType = curl_getinfo($curlHandle, CURLINFO_CONTENT_TYPE);

        return new HttpResponse(
            (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE),
            $request->getUrl(),
            is_string($contentType) ? $contentType : null,
            $response,
            $responseHeaders,
        );
    }

    /**
     * @return CurlHandle Дескриптор curl: общий для всех запросов либо новый.
     */
    private function getCurlHandle(): CurlHandle
    {
        if ($this->usePersistentCurlHandle) {
            $this->curlHandle ??= curl_init() ?: throw new LogicException('Failed to initialize curl.');
            curl_reset($this->curlHandle);

            return $this->curlHandle;
        }

        return curl_init() ?: throw new LogicException('Failed to initialize curl.');
    }

    /**
     * Проверяет, что код ошибки curl относится к ошибкам сети.
     *
     * @param int $curlErrno Код ошибки curl.
     * @return bool `true`, если ответ не получен из-за ошибки сети.
     */
    private static function isNetworkError(int $curlErrno): bool
    {
        return in_array($curlErrno, [
            NetworkException::UNSUPPORTED_PROTOCOL,
            NetworkException::FAILED_INIT,
            NetworkException::URL_MALFORMAT,
            NetworkException::COULDNT_RESOLVE_HOST,
            NetworkException::COULDNT_CONNECT,
            NetworkException::WRITE_ERROR,
            NetworkException::READ_ERROR,
            NetworkException::OUT_OF_MEMORY,
            NetworkException::OPERATION_TIMEOUTED,
            NetworkException::HTTP_RANGE_ERROR,
            NetworkException::SSL_CONNECT_ERROR,
            NetworkException::ABORTED_BY_CALLBACK,
            NetworkException::HTTP_PORT_FAILED,
            NetworkException::TOO_MANY_REDIRECTS,
            NetworkException::GOT_NOTHING,
            NetworkException::SEND_ERROR,
            NetworkException::RECV_ERROR,
            NetworkException::SSL_CERTPROBLEM,
            NetworkException::SSL_CIPHER,
            NetworkException::SSL_PEER_CERTIFICATE,
            NetworkException::BAD_CONTENT_ENCODING,
            NetworkException::FILESIZE_EXCEEDED,
        ], true);
    }
}
