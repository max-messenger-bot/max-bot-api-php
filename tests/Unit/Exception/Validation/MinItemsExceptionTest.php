<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinItemsException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class MinItemsExceptionTest extends Unit
{
    /**
     * @return iterable<string, array{int, int, string}>
     */
    public static function messages(): iterable
    {
        yield 'непустой массив, передан пустой' => [
            0,
            1,
            'Argument "items" expects a non-empty array, but an empty array was passed.',
        ];
        yield 'не меньше двух, передан пустой' => [
            0,
            2,
            'Argument "items" expects an array of at least 2 items, but an empty array was passed.',
        ];
        yield 'не меньше трёх, передан один' => [
            1,
            3,
            'Argument "items" expects an array of at least 3 items, but an array of 1 items was passed.',
        ];
    }

    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new MinItemsException('items', 0, 1));
    }

    /**
     * @dataProvider messages
     */
    public function testMessage(int $countItems, int $minItems, string $expected): void
    {
        $exception = new MinItemsException('items', $countItems, $minItems);

        self::assertSame($expected, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
