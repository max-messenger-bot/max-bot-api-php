<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use Closure;
use JsonException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use MaxMessenger\Bot\Exception\AccessTokenException;
use MaxMessenger\Bot\HttpClient\Body\StringBody;
use MaxMessenger\Bot\HttpClient\Exception\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\HttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\JsonDecodeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\UnexpectedContentTypeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\UnexpectedFormatException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\Model\Response\Error;
use Throwable;

use function array_shift;
use function http_build_query;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function str_starts_with;
use function usleep;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

/**
 * HTTP-клиент для API Max.
 */
final class MaxHttpClient implements MaxHttpClientInterface
{
    /**
     * Создаёт клиент для запросов к API с настройками соединения и повторов.
     *
     * @param MaxApiConfigInterface $config Настройки подключения к API.
     * @param non-empty-string|null $version Версия API для параметра строки запроса `v`.
     * @param Closure(non-empty-string $method, TemporaryExceptionInterface $exception): void|null $exceptionLogger
     *     Обработчик временных ошибок перед повторной попыткой запроса или `null` для отключения логирования.
     * @param HttpClientInterface|null $httpClient Клиент, выполняющий запросы.
     * @param HttpClientInterface|null $httpClientForUpdates Клиент для длинного опроса событий.
     * @param HttpClientInterface|null $httpClientForUploader Клиент для загрузки медиафайлов.
     */
    public function __construct(
        private readonly MaxApiConfigInterface $config,
        public ?string $version = null,
        private ?Closure $exceptionLogger = null,
        private ?HttpClientInterface $httpClient = null,
        private ?HttpClientInterface $httpClientForUpdates = null,
        private ?HttpClientInterface $httpClientForUploader = null,
    ) {}

    /**
     * Выполняет запрос `DELETE`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function delete(string $path, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, 'DELETE'));
    }

    /**
     * Выполняет запрос `GET`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function get(string $path, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, 'GET'));
    }

    /**
     * @return HttpClientInterface Клиент, выполняющий запросы.
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient ??= $this->config->getHttpClient();
    }

    /**
     * @return HttpClientInterface Клиент для длинного опроса событий.
     */
    public function getHttpClientForUpdates(): HttpClientInterface
    {
        return $this->httpClientForUpdates ??= $this->config->getHttpClientForUpdates() ?? $this->getHttpClient();
    }

    /**
     * @return HttpClientInterface Клиент для загрузки медиафайлов.
     */
    public function getHttpClientForUploader(): HttpClientInterface
    {
        return $this->httpClientForUploader ??= $this->config->getHttpClientForUploader();
    }

    /**
     * Получает обновления через клиент для длинного опроса.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @param positive-int|null $timeout Максимальное время выполнения запроса в миллисекундах.
     * @return array Тело ответа.
     */
    public function getUpdates(string $path, ?array $query = null, ?int $timeout = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, 'GET', null, $timeout), true);
    }

    /**
     * Выполняет запрос `PATCH`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param object $body Тело запроса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function patch(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, 'PATCH', $body));
    }

    /**
     * Выполняет запрос `POST`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param object|null $body Тело запроса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function post(string $path, ?object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, 'POST', $body));
    }

    /**
     * Выполняет запрос `PUT`.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param object $body Тело запроса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @return array Тело ответа.
     */
    public function put(string $path, object $body, ?array $query = null): array
    {
        return $this->doRequest($this->makeRequest($path, $query, 'PUT', $body));
    }

    /**
     * @param Closure(non-empty-string $method, TemporaryExceptionInterface $exception): void|null $exceptionLogger
     *     Обработчик временных ошибок перед повторной попыткой запроса или `null` для отключения логирования.
     * @return $this
     */
    public function setExceptionLogger(?Closure $exceptionLogger): static
    {
        $this->exceptionLogger = $exceptionLogger;

        return $this;
    }

    /**
     * @param non-empty-string|null $version Версия API для параметра строки запроса `v` или `null`,
     *     чтобы не передавать его.
     * @return $this
     */
    public function setVersion(?string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Разбирает тело ответа.
     *
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     * @return mixed Разобранное тело ответа.
     * @throws UnexpectedContentTypeException Если сервер вернул не JSON.
     * @throws JsonDecodeException Если тело ответа не удалось разобрать.
     */
    private function decodeBody(HttpRequestInterface $request, HttpResponseInterface $response): mixed
    {
        if (!str_starts_with($response->getContentType() ?? '', 'application/json')) {
            throw new UnexpectedContentTypeException($request, $response);
        }

        try {
            return json_decode($response->getBody(), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonDecodeException($request, $response, $e);
        }
    }

    private function doRequest(HttpRequest $request, bool $forUpdates = false): array
    {
        $httpClient = $forUpdates ? $this->getHttpClientForUpdates() : $this->getHttpClient();

        $retryAttempts = $request->getMethod() === 'POST'
            ? $this->config->getPostRetryAttempts()
            : $this->config->getRetryAttempts();
        do {
            try {
                $response = $httpClient->request($request);

                if ($response->getHttpCode() !== 200) {
                    HttpException::throw($request, $response, $this->parseError($request, $response));
                }

                $responseData = $this->decodeBody($request, $response);

                if (!is_array($responseData)) {
                    throw new UnexpectedFormatException($request, $response);
                }

                return $responseData;
            } catch (TemporaryExceptionInterface $e) {
                if (!$retryAttempts) {
                    throw $e;
                }
                if ($this->exceptionLogger !== null) {
                    ($this->exceptionLogger)(__METHOD__, $e);
                }
                usleep(array_shift($retryAttempts) * 1000);
            }
        } while (true);
    }

    /**
     * @param object $body Тело запроса.
     * @return string Тело запроса, закодированное в JSON.
     * @throws JsonEncodeException Если тело запроса не удалось закодировать.
     */
    private function encodeBody(object $body): string
    {
        try {
            return json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonEncodeException($body, $e);
        }
    }

    /**
     * Готовит запрос к API: собирает адрес со строкой параметров, заголовки и тело.
     *
     * @param non-empty-string $path Путь запроса относительно базового адреса.
     * @param array<string|int>|null $query Параметры строки запроса.
     * @param non-empty-string $method Метод запроса.
     * @param object|null $body Тело запроса. Кодируется в JSON.
     * @param positive-int|null $timeout Максимальное время выполнения запроса в миллисекундах.
     * @throws AccessTokenException Если токен доступа не задан.
     * @throws JsonEncodeException Если тело запроса не удалось закодировать в JSON.
     */
    private function makeRequest(
        string $path,
        ?array $query,
        string $method,
        ?object $body = null,
        ?int $timeout = null,
    ): HttpRequest {
        $accessToken = $this->config->getAccessToken() ?? throw new AccessTokenException();
        $accessTokenValue = $accessToken->getValue();

        /** @psalm-suppress DocblockTypeContradiction Значение токена приходит извне. */
        if (!is_string($accessTokenValue) || $accessTokenValue === '') {
            throw new AccessTokenException();
        }

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if ($this->version) {
            if ($query !== null) {
                $query['v'] ??= $this->version;
            } else {
                $query = ['v' => $this->version];
            }
        }

        $url = $this->config->getBaseUrl() . $path;

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if ($query) {
            /**
             * @psalm-taint-escape ssrf
             * @psalm-taint-escape file
             */
            $queryString = http_build_query($query);
            $url .= '?' . $queryString;
        }

        return new HttpRequest(
            $method,
            $url,
            ['Accept' => 'application/json; charset=utf-8'],
            $accessToken,
            $body !== null ? new StringBody($this->encodeBody($body), 'application/json; charset=utf-8') : null,
            $timeout,
        );
    }

    /**
     * Разбирает описание ошибки из тела ответа.
     *
     * @param HttpRequestInterface $request Запрос, на который получен ответ.
     * @param HttpResponseInterface $response Ответ сервера.
     * @return Error|null Описание ошибки или `null`, если тело ответа разобрать не удалось.
     */
    private function parseError(HttpRequestInterface $request, HttpResponseInterface $response): ?Error
    {
        try {
            $error = Error::newFromData((array) $this->decodeBody($request, $response));
        } catch (Throwable) {
            return null;
        }

        return $error->isValid() ? $error : null;
    }
}
