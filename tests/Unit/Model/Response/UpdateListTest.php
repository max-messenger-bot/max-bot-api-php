<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\BotStartedUpdate;
use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\UpdateList;

final class UpdateListTest extends Unit
{
    public function testEmptyList(): void
    {
        $list = UpdateList::newFromData(['updates' => [], 'marker' => null]);

        self::assertSame([], $list->getUpdates());
        self::assertNull($list->getMarker());
    }

    public function testGetMarker(): void
    {
        self::assertSame(42, UpdateList::newFromData(['updates' => [], 'marker' => 42])->getMarker());
        self::assertNull(UpdateList::newFromData(['updates' => []])->getMarker());
    }

    public function testGetUpdates(): void
    {
        $list = UpdateList::newFromData([
            'updates' => [
                [
                    'update_type' => 'bot_started',
                    'timestamp' => 1_700_000_000_000,
                    'chat_id' => 100,
                    'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                ],
                [
                    'update_type' => 'message_removed',
                    'timestamp' => 1_700_000_000_001,
                    'message_id' => 'mid.1',
                    'chat_id' => 100,
                    'user_id' => 200,
                ],
                ['update_type' => 'future_update', 'timestamp' => 1_700_000_000_002],
            ],
            'marker' => 42,
        ]);

        $updates = $list->getUpdates();

        self::assertCount(3, $updates);
        self::assertInstanceOf(BotStartedUpdate::class, $updates[0]);
        self::assertInstanceOf(MessageRemovedUpdate::class, $updates[1]);
        self::assertSame(Update::class, $updates[2]::class);
        self::assertSame(1_700_000_000_002, $updates[2]->getTimestampRaw());
        self::assertSame($updates, $list->getUpdates());
    }
}
