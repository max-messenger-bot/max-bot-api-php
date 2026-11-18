<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception;

use Codeception\Test\Unit;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\Exception\TimeoutException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use RuntimeException;

final class TimeoutExceptionTest extends Unit
{
    public function testIsTemporaryNetworkException(): void
    {
        $exception = new TimeoutException(new RuntimeException('timed out'), new HttpRequest('GET', 'https://example.test/updates'));

        self::assertInstanceOf(TemporaryNetworkException::class, $exception);
    }
}
