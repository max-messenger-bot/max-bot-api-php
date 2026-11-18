<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiLogicException;
use MaxMessenger\Bot\Exception\MaxBot\Event\PostIdMissingException;

final class PostIdMissingExceptionTest extends Unit
{
    public function testExtendsMaxApiLogicException(): void
    {
        self::assertInstanceOf(MaxApiLogicException::class, new PostIdMissingException());
    }

    public function testMessage(): void
    {
        $exception = new PostIdMissingException();

        self::assertSame('Post id missing.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
