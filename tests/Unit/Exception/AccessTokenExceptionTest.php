<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\AccessTokenException;
use MaxMessenger\Bot\Exception\MaxApiException;

final class AccessTokenExceptionTest extends Unit
{
    public function testExtendsMaxApiException(): void
    {
        self::assertInstanceOf(MaxApiException::class, new AccessTokenException());
    }

    public function testMessage(): void
    {
        $exception = new AccessTokenException();

        self::assertSame('Access token not set.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
