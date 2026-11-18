<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiException;
use MaxMessenger\Bot\Exception\Validation\KeyboardException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class KeyboardExceptionTest extends Unit
{
    public function testExtendsValidationException(): void
    {
        $exception = new KeyboardException();

        self::assertInstanceOf(ValidationException::class, $exception);
        self::assertInstanceOf(MaxApiException::class, $exception);
    }
}
