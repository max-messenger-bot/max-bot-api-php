<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class MinLengthExceptionTest extends Unit
{
    /**
     * @return iterable<string, array{int, int, string}>
     */
    public static function messages(): iterable
    {
        yield 'непустая строка, передана пустая' => [
            0,
            1,
            'Argument "text" expects a non-empty string, but an empty string was passed.',
        ];
        yield 'не короче двух, передана пустая' => [
            0,
            2,
            'Argument "text" expects a string of at least 2 characters, but an empty string was passed.',
        ];
        yield 'не короче трёх, передан один символ' => [
            1,
            3,
            'Argument "text" expects a string of at least 3 characters, but a string of 1 characters was passed.',
        ];
    }

    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new MinLengthException('text', 0, 1));
    }

    /**
     * @dataProvider messages
     */
    public function testMessage(int $length, int $minLength, string $expected): void
    {
        $exception = new MinLengthException('text', $length, $minLength);

        self::assertSame($expected, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
