<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\GetPinnedMessageResult;

final class GetPinnedMessageResultTest extends Unit
{
    public function testGetMessage(): void
    {
        $result = GetPinnedMessageResult::newFromData([
            'message' => [
                'recipient' => ['chat_id' => 100, 'chat_type' => 'chat'],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.pinned', 'seq' => 1, 'text' => 'Закреплено'],
            ],
        ]);

        $message = $result->getMessage();

        self::assertNotNull($message);
        self::assertSame('mid.pinned', $message->getBody()->getMid());
        self::assertSame($message, $result->getMessage());
    }

    public function testNullMessage(): void
    {
        $result = GetPinnedMessageResult::newFromData(['message' => null]);

        self::assertNull($result->getMessage());
        self::assertNull($result->getMessage());
    }

    public function testWithoutMessage(): void
    {
        self::assertNull(GetPinnedMessageResult::newFromData([])->getMessage());
    }
}
