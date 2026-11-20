<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\CurlHttpClient;

use Closure;
use CurlHandle;
use LogicException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\Contract\Http\StreamInterface;
use MaxMessenger\Bot\CurlHttpClient\Exception\CurlException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\Exception\TimeoutException;
use MaxMessenger\Bot\HttpClient\HttpResponse;
use MaxMessenger\Bot\HttpClient\TransportErrorType;

use function array_replace;
use function count;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_reset;
use function curl_setopt_array;
use function defined;
use function explode;
use function in_array;
use function is_string;
use function str_starts_with;
use function strlen;
use function trim;

/**
 * HTTP-клиент на основе curl.
 *
 * Выполняет запросы {@see HttpRequestInterface} и возвращает {@see HttpResponse}.
 * Настройки curl задаются методами `set*`, произвольные параметры — методом {@see setOption()}.
 */
final class CurlHttpClient implements HttpClientInterface
{
    /**
     * @var int Максимальный размер потокового тела в байтах, передаваемого строкой через `CURLOPT_POSTFIELDS`.
     */
    public int $postDataStringThreshold = 256 * 1024;
    /**
     * @var bool Сохранять дескриптор curl между запросами, чтобы curl мог повторно использовать соединение.
     */
    public bool $usePersistentCurlHandle = true;
    private ?CurlHandle $curlHandle = null;

    /**
     * @param array<int, mixed> $options Параметры curl.
     * @param (Closure(CurlException): ?TransportErrorType)|null $errorClassifier Определитель ошибок curl.
     *     `null` в результате вызывает встроенное правило; {@see TransportErrorType::Permanent} отключает повтор.
     */
    public function __construct(
        private array $options = [],
        private ?Closure $errorClassifier = null,
    ) {}

    /**
     * @return array<int, mixed> Параметры curl.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Выполняет запрос к API через curl.
     *
     * @param HttpRequestInterface $request Запрос к API.
     * @return HttpResponseInterface Ответ сервера.
     */
    public function request(HttpRequestInterface $request): HttpResponseInterface
    {
        $method = $request->getMethod();
        $headers = $request->getHeaders();
        $body = $request->getBody();

        $options = array_replace($this->options, [
            CURLOPT_URL => $request->getUrl(),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => $method === 'HEAD',
        ]);

        $stream = null;

        if ($body !== null) {
            $headers['Content-Type'] = $body->getContentType();

            $content = $body->getBody();
            if ($content instanceof StreamInterface) {
                $content->seek(0);

                if ($content->getSize() <= $this->postDataStringThreshold) {
                    $options[CURLOPT_POSTFIELDS] = $content->getContents();
                } else {
                    $stream = $content;
                }
            } else {
                $options[CURLOPT_POSTFIELDS] = $content;
            }
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options[CURLOPT_POSTFIELDS] = '';
        }

        $curlHeaders = [];
        foreach ($headers as $name => $value) {
            $curlHeaders[] = $name . ': ' . $value;
        }

        if ($stream !== null) {
            $options[CURLOPT_UPLOAD] = true;
            $options[CURLOPT_INFILESIZE] = $stream->getSize();
            $options[CURLOPT_READFUNCTION] = static fn(CurlHandle $curlHandle, mixed $fileHandle, int $length): string
                => $length > 0 ? $stream->read($length) : '';

            // Заголовок без значения убирает `Expect: 100-continue`, который curl добавляет
            // к большому телу: иначе перед отправкой данных он ждёт ответа сервера.
            $curlHeaders[] = 'Expect:';
        }

        $options[CURLOPT_HTTPHEADER] = $curlHeaders;

        $timeout = $request->getTimeout();
        if ($timeout !== null) {
            unset($options[CURLOPT_TIMEOUT]);
            $options[CURLOPT_TIMEOUT_MS] = $timeout;
        }

        $lowSpeedLimit = $request->getLowSpeedLimit();
        $lowSpeedTime = $request->getLowSpeedTime();

        if ($lowSpeedLimit !== null && $lowSpeedTime !== null) {
            $options[CURLOPT_LOW_SPEED_LIMIT] = $lowSpeedLimit;
            $options[CURLOPT_LOW_SPEED_TIME] = $lowSpeedTime;
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
     * @param non-empty-string|null $caCertificateDir Путь к папке с сертификатами удостоверяющих центров
     *     (`CURLOPT_CAPATH`) или `null`, чтобы использовать системные сертификаты.
     * @return $this
     */
    public function setCaCertificateDir(?string $caCertificateDir): static
    {
        return $this->setOption(CURLOPT_CAPATH, $caCertificateDir);
    }

    /**
     * @param non-empty-string|null $caCertificatePath Путь к файлу с сертификатами удостоверяющих центров
     *     (`CURLOPT_CAINFO`) или `null`, чтобы использовать системные сертификаты.
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
     * @param (Closure(CurlException): ?TransportErrorType)|null $errorClassifier Определитель ошибок curl.
     *     `null` удаляет callback; `null` в результате вызывает встроенное правило.
     * @return $this
     */
    public function setErrorClassifier(?Closure $errorClassifier): static
    {
        $this->errorClassifier = $errorClassifier;

        return $this;
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
     * Настраивает использование системного хранилища сертификатов, если это поддерживается libcurl.
     *
     * @param bool $useNativeCaCertificates Включить или отключить `CURLSSLOPT_NATIVE_CA`.
     * @return $this
     */
    public function setUseNativeCaCertificates(bool $useNativeCaCertificates): static
    {
        if (!defined('CURLSSLOPT_NATIVE_CA')) {
            return $this;
        }

        $sslOptions = (int) ($this->options[CURLOPT_SSL_OPTIONS] ?? 0);
        $sslOptions = $useNativeCaCertificates
            ? $sslOptions | CURLSSLOPT_NATIVE_CA
            : $sslOptions & ~CURLSSLOPT_NATIVE_CA;

        return $this->setOption(CURLOPT_SSL_OPTIONS, $sslOptions !== 0 ? $sslOptions : null);
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
     * @param bool $verifySslCertificate Нужно ли проверять TLS-сертификат сервера. `false` отключает проверку.
     * @return $this
     */
    public function setVerifySslCertificate(bool $verifySslCertificate): static
    {
        return $this
            ->setOption(CURLOPT_SSL_VERIFYPEER, $verifySslCertificate)
            ->setOption(CURLOPT_SSL_VERIFYHOST, $verifySslCertificate ? 2 : 0);
    }

    private static function classifyError(CurlException $exception): ?TransportErrorType
    {
        $code = $exception->getCode();

        if ($code === CURLE_OPERATION_TIMEOUTED) {
            return TransportErrorType::Timeout;
        }

        return in_array($code, [
            CURLE_COULDNT_RESOLVE_PROXY,
            CURLE_COULDNT_RESOLVE_HOST,
            CURLE_COULDNT_CONNECT,
            CURLE_PARTIAL_FILE,
            CURLE_SSL_CONNECT_ERROR,
            CURLE_GOT_NOTHING,
            CURLE_SEND_ERROR,
            CURLE_RECV_ERROR,
        ], true) ? TransportErrorType::TemporaryNetwork : null;
    }

    /**
     * Выполняет запрос.
     *
     * @param array<int, mixed> $options Параметры curl.
     * @param HttpRequestInterface $request Запрос.
     * @return HttpResponseInterface Ответ сервера.
     * @throws TimeoutException Если истекло время ожидания.
     * @throws TemporaryNetworkException Если соединение или передача временно не удались.
     * @throws CurlException При остальных ошибках curl.
     */
    private function execute(array $options, HttpRequestInterface $request): HttpResponseInterface
    {
        $responseHeaders = [];

        $headerFunction = static function (CurlHandle $curlHandle, string $header) use (&$responseHeaders): int {
            if (str_starts_with($header, 'HTTP/')) {
                $responseHeaders = [];

                return strlen($header);
            }

            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $responseHeaders[trim($parts[0])][] = trim($parts[1]);
            }

            return strlen($header);
        };
        $options[CURLOPT_HEADERFUNCTION] = $headerFunction;

        $curlHandle = $this->getCurlHandle();

        curl_setopt_array($curlHandle, $options);

        $response = curl_exec($curlHandle);

        if (!is_string($response)) {
            $previous = new CurlException(curl_error($curlHandle), curl_errno($curlHandle), $request);
            $type = $this->errorClassifier?->__invoke($previous) ?? self::classifyError($previous);

            throw match ($type) {
                TransportErrorType::Timeout => new TimeoutException($previous, $request),
                TransportErrorType::TemporaryNetwork => new TemporaryNetworkException($previous, $request),
                default => $previous,
            };
        }

        /** @var string|false|null $contentType */
        $contentType = curl_getinfo($curlHandle, CURLINFO_CONTENT_TYPE);

        return new HttpResponse(
            (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE),
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
}
