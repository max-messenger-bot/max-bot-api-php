<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use LogicException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Фейковый клиент PSR-18: запоминает отправленный запрос и возвращает заранее заданный ответ
 * либо выбрасывает заданное исключение.
 */
final class FakePsrClient implements ClientInterface
{
    public ?RequestInterface $lastRequest = null;

    public function __construct(
        private readonly ?ResponseInterface $response = null,
        private readonly ?Throwable $exception = null,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;

        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->response ?? throw new LogicException('Response is not configured.');
    }
}
