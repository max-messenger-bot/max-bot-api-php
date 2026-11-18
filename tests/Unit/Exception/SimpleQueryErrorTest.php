<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiException;
use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\Model\Response\SimpleQueryResult;

final class SimpleQueryErrorTest extends Unit
{
    public function testExtendsMaxApiException(): void
    {
        self::assertInstanceOf(MaxApiException::class, new SimpleQueryError(SimpleQueryResult::newFromData([])));
    }

    public function testKeepsMessage(): void
    {
        $exception = new SimpleQueryError(SimpleQueryResult::newFromData(['message' => 'Chat not found']));

        self::assertSame('Chat not found', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
        self::assertNull($exception->getErrorCode());
    }

    public function testKeepsOriginalResult(): void
    {
        $result = SimpleQueryResult::newFromData(['success' => false, 'code' => 'not.found', 'extra' => [1, 2]]);
        $exception = new SimpleQueryError($result);

        self::assertSame($result, $exception->getResult());
        self::assertSame('not.found', $exception->getErrorCode());
    }
}
