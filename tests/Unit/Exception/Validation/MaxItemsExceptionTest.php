<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxItemsException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class MaxItemsExceptionTest extends Unit
{
    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new MaxItemsException('buttons', 8, 7));
    }

    public function testMessage(): void
    {
        $exception = new MaxItemsException('buttons', 8, 7);

        self::assertSame(
            'Argument "buttons" expects an array of up to 7 items, but an array of 8 items was passed.',
            $exception->getMessage(),
        );
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
