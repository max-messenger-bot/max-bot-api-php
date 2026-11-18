<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiException;
use MaxMessenger\Bot\HttpClient\Exception\HttpClientException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\Tests\Support\Fake\FakeStringCodeException;
use RuntimeException;

final class TemporaryNetworkExceptionTest extends Unit
{
    public function testCastsNonIntegerCodeOfPrevious(): void
    {
        $previous = new FakeStringCodeException('SQLSTATE[08006] Connection failure', '08006');

        $exception = new TemporaryNetworkException($previous, new HttpRequest('GET', 'https://example.test/me'));

        self::assertSame(8006, $exception->getCode());
        self::assertSame('SQLSTATE[08006] Connection failure', $exception->getMessage());
    }

    public function testHierarchy(): void
    {
        $exception = $this->createException();

        self::assertInstanceOf(HttpClientException::class, $exception);
        self::assertInstanceOf(MaxApiException::class, $exception);
        self::assertInstanceOf(TemporaryExceptionInterface::class, $exception);
    }

    public function testTakesMessageAndCodeFromPrevious(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $previous = new RuntimeException('Connection refused', 7);

        $exception = new TemporaryNetworkException($previous, $request);

        self::assertSame('Connection refused', $exception->getMessage());
        self::assertSame(7, $exception->getCode());
        self::assertSame($request, $exception->getRequest());
        self::assertSame($request, $exception->request);
        self::assertSame($previous, $exception->getPrevious());
    }

    private function createException(): TemporaryNetworkException
    {
        return new TemporaryNetworkException(
            new RuntimeException('Connection refused', 7),
            new HttpRequest('GET', 'https://example.test/me'),
        );
    }
}
