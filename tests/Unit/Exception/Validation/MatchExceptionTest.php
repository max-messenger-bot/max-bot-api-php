<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class MatchExceptionTest extends Unit
{
    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new MatchException('name'));
    }

    public function testMessage(): void
    {
        $exception = new MatchException('name');

        self::assertSame('Argument "name" has an invalid value.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
