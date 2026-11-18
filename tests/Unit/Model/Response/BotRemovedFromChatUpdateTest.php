<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class BotRemovedFromChatUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate(isChannel: false);

        self::assertSame(100, $update->getChatId());
        self::assertFalse($update->isChannel());
        self::assertSame(200, $update->getUser()->getUserId());
        self::assertSame($update->getUser(), $update->getUser());
    }

    public function testIsChannel(): void
    {
        self::assertTrue($this->createUpdate(isChannel: true)->isChannel());
    }

    private function createUpdate(bool $isChannel): BotRemovedFromChatUpdate
    {
        $update = Update::newFromData([
            'update_type' => 'bot_removed',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'is_channel' => $isChannel,
        ]);
        self::assertInstanceOf(BotRemovedFromChatUpdate::class, $update);

        return $update;
    }
}
