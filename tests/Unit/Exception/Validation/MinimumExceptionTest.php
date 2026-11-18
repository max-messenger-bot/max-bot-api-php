<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinimumException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class MinimumExceptionTest extends Unit
{
    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new MinimumException('count', 0, 1));
    }

    public function testMessage(): void
    {
        $exception = new MinimumException('count', 0, 1);

        self::assertSame(
            'Argument "count" expects a number greater than or equal to 1, but 0 was passed.',
            $exception->getMessage(),
        );
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testMessageWithNegativeNumbers(): void
    {
        $exception = new MinimumException('offset', -10, -5);

        self::assertSame(
            'Argument "offset" expects a number greater than or equal to -5, but -10 was passed.',
            $exception->getMessage(),
        );
    }
}
