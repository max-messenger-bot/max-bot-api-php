<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiLogicException;
use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;

final class SenderUnknownExceptionTest extends Unit
{
    public function testExtendsMaxApiLogicException(): void
    {
        self::assertInstanceOf(MaxApiLogicException::class, new SenderUnknownException());
    }

    public function testMessage(): void
    {
        $exception = new SenderUnknownException();

        self::assertSame('The sender is unknown.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
