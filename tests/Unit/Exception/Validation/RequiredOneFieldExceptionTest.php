<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\Validation;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Exception\Validation\ValidationException;

final class RequiredOneFieldExceptionTest extends Unit
{
    /**
     * @return iterable<string, array{list<string>, string}>
     */
    public static function messages(): iterable
    {
        yield 'одно поле' => [['text'], 'At least one field of "text" cannot be null.'];
        yield 'несколько полей' => [
            ['text', 'attachments', 'link'],
            'At least one field of "text", "attachments", "link" cannot be null.',
        ];
    }

    public function testExtendsValidationException(): void
    {
        self::assertInstanceOf(ValidationException::class, new RequiredOneFieldException(['text']));
    }

    /**
     * @param list<string> $fieldNames
     * @dataProvider messages
     */
    public function testMessage(array $fieldNames, string $expected): void
    {
        $exception = new RequiredOneFieldException($fieldNames);

        self::assertSame($expected, $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
