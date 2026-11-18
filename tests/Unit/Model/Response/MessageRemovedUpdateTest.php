<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class MessageRemovedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = Update::newFromData([
            'update_type' => 'message_removed',
            'timestamp' => 1_700_000_000_000,
            'message_id' => 'mid.1',
            'chat_id' => 100,
            'user_id' => 200,
        ]);
        self::assertInstanceOf(MessageRemovedUpdate::class, $update);

        self::assertSame(100, $update->getChatId());
        self::assertSame('mid.1', $update->getMessageId());
        self::assertSame(200, $update->getUserId());
    }
}
