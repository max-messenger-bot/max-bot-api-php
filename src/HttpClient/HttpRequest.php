<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use MaxMessenger\Bot\Contract\Http\BodyInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use SensitiveParameterValue;

/**
 * HTTP-запрос.
 */
final readonly class HttpRequest implements HttpRequestInterface
{
    /**
     * @param non-empty-string $method Метод запроса, например `GET` или `POST`.
     * @param non-empty-string $url Адрес запроса вместе со строкой параметров.
     * @param array<non-empty-string, non-empty-string> $headers Заголовки запроса: имя заголовка и его значение.
     * @param SensitiveParameterValue<non-empty-string>|null $accessToken Токен для заголовка `Authorization`.
     * @param BodyInterface|null $body Тело запроса или `null`, если тела нет.
     * @param non-negative-int|null $timeout Максимальное время выполнения запроса в миллисекундах.
     * @param positive-int|null $lowSpeedLimit Наименьшая допустимая скорость передачи в байтах в секунду.
     * @param positive-int|null $lowSpeedTime Сколько секунд скорость может держаться ниже предела.
     */
    public function __construct(
        private string $method,
        private string $url,
        private array $headers = [],
        private ?SensitiveParameterValue $accessToken = null,
        private ?BodyInterface $body = null,
        private ?int $timeout = null,
        private ?int $lowSpeedLimit = null,
        private ?int $lowSpeedTime = null,
    ) {}

    /**
     * @return BodyInterface|null Тело запроса или `null`, если тела нет.
     */
    public function getBody(): ?BodyInterface
    {
        return $this->body;
    }

    /**
     * @return array<non-empty-string, non-empty-string> Заголовки запроса: имя заголовка и его значение.
     */
    public function getHeaders(): array
    {
        $headers = $this->headers;

        if ($this->accessToken !== null) {
            $headers['Authorization'] = $this->accessToken->getValue();
        }

        return $headers;
    }

    /**
     * @return positive-int|null Наименьшая допустимая скорость передачи в байтах в секунду. Задаётся вместе
     *     с {@see getLowSpeedTime()}: если хотя бы одно из двух значений `null`, скорость не проверяется.
     */
    public function getLowSpeedLimit(): ?int
    {
        return $this->lowSpeedLimit;
    }

    /**
     * @return positive-int|null Сколько секунд скорость может держаться ниже предела,
     *     прежде чем передача будет прервана.
     */
    public function getLowSpeedTime(): ?int
    {
        return $this->lowSpeedTime;
    }

    /**
     * @return non-empty-string Метод запроса в верхнем регистре, например `GET` или `POST`.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return non-negative-int|null Максимальное время выполнения запроса в миллисекундах.
     *     `0` — ждать неограниченно, `null` — использовать значение клиента.
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * @return non-empty-string Адрес запроса вместе со строкой параметров.
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
