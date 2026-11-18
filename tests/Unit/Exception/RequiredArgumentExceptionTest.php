<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception;

use Codeception\Test\Unit;
use InvalidArgumentException;
use MaxMessenger\Bot\Exception\RequiredArgumentException;

final class RequiredArgumentExceptionTest extends Unit
{
    public function testExtendsInvalidArgumentException(): void
    {
        self::assertInstanceOf(InvalidArgumentException::class, new RequiredArgumentException('userId'));
    }

    public function testMessage(): void
    {
        $exception = new RequiredArgumentException('userId');

        self::assertSame('The $userId argument cannot be null.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
