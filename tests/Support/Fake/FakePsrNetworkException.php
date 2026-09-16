<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * Сетевая ошибка клиента PSR-18 для тестов.
 */
final class FakePsrNetworkException extends RuntimeException implements NetworkExceptionInterface
{
    public function __construct(
        string $message,
        private readonly RequestInterface $request,
    ) {
        parent::__construct($message);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
