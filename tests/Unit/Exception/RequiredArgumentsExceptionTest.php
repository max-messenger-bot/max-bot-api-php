<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception;

use Codeception\Test\Unit;
use InvalidArgumentException;
use MaxMessenger\Bot\Exception\RequiredArgumentsException;

final class RequiredArgumentsExceptionTest extends Unit
{
    public function testCustomMessage(): void
    {
        $exception = new RequiredArgumentsException('Only one of the arguments (userId, chatId) must be non-null.');

        self::assertSame('Only one of the arguments (userId, chatId) must be non-null.', $exception->getMessage());
    }

    public function testDefaultMessage(): void
    {
        $exception = new RequiredArgumentsException();

        self::assertSame('At least one argument must be non-null.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testEmptyMessageUsesDefault(): void
    {
        $exception = new RequiredArgumentsException('');

        self::assertSame('At least one argument must be non-null.', $exception->getMessage());
    }

    public function testExtendsInvalidArgumentException(): void
    {
        self::assertInstanceOf(InvalidArgumentException::class, new RequiredArgumentsException());
    }

    public function testZeroMessageIsKept(): void
    {
        $exception = new RequiredArgumentsException('0');

        self::assertSame('0', $exception->getMessage());
    }
}
