<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaximumException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class MaximumExceptionTest extends Unit
{
    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new MaximumException('count', 101, 100));
    }

    public function testMessage(): void
    {
        $exception = new MaximumException('count', 101, 100);

        self::assertSame(
            'Argument "count" expects a number less than or equal to 100, but 101 was passed.',
            $exception->getMessage(),
        );
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testMessageWithNegativeNumbers(): void
    {
        $exception = new MaximumException('offset', -1, -5);

        self::assertSame(
            'Argument "offset" expects a number less than or equal to -5, but -1 was passed.',
            $exception->getMessage(),
        );
    }
}
