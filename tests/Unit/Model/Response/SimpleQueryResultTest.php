<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\SimpleQueryResult;

final class SimpleQueryResultTest extends Unit
{
    public function testSuccess(): void
    {
        $result = SimpleQueryResult::newFromData(['success' => true]);

        self::assertTrue($result->isSuccess());
        self::assertNull($result->getCode());
        self::assertNull($result->getMessage());
    }

    /**
     * @param array<string, mixed> $data
     * @dataProvider unsuccessfulData
     */
    public function testUnsuccessful(array $data): void
    {
        self::assertFalse(SimpleQueryResult::newFromData($data)->isSuccess());
    }

    public function testUnsuccessfulWithCode(): void
    {
        $result = SimpleQueryResult::newFromData([
            'success' => false,
            'code' => 'not.found',
            'message' => 'Resource not found',
        ]);

        self::assertFalse($result->isSuccess());
        self::assertSame('not.found', $result->getCode());
    }

    public function testUnsuccessfulWithMessage(): void
    {
        $result = SimpleQueryResult::newFromData(['success' => false, 'message' => 'Ошибка']);

        self::assertSame('Ошибка', $result->getMessage());
        self::assertNull($result->getCode());
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function unsuccessfulData(): iterable
    {
        yield 'success = false' => [['success' => false, 'message' => 'Ошибка']];
        yield 'нет success' => [[]];
        yield 'success строкой' => [['success' => 'true']];
        yield 'success числом' => [['success' => 1]];
    }
}
