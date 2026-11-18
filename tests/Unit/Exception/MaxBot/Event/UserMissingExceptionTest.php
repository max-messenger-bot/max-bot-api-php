<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiLogicException;
use MaxMessenger\Bot\Exception\MaxBot\Event\UserMissingException;

final class UserMissingExceptionTest extends Unit
{
    public function testExtendsMaxApiLogicException(): void
    {
        self::assertInstanceOf(MaxApiLogicException::class, new UserMissingException());
    }

    public function testMessage(): void
    {
        $exception = new UserMissingException();

        self::assertSame('User missing.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
