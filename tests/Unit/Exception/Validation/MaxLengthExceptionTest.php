<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class MaxLengthExceptionTest extends Unit
{
    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new MaxLengthException('text', 4001, 4000));
    }

    public function testMessage(): void
    {
        $exception = new MaxLengthException('text', 4001, 4000);

        self::assertSame(
            'Argument "text" expects a string of up to 4000 characters, but a string of 4001 characters was passed.',
            $exception->getMessage(),
        );
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
