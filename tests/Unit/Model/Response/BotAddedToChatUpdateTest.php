<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class BotAddedToChatUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate(isChannel: false);

        self::assertSame(100, $update->getChatId());
        self::assertFalse($update->isChannel());
        self::assertTrue($update->isValid());
        self::assertSame(['chat_id', 'is_channel'], $update->getRequiredProperties());
        self::assertNull($update->getUser());
        self::assertNull($update->getUser());
    }

    public function testIsChannel(): void
    {
        self::assertTrue($this->createUpdate(isChannel: true)->isChannel());
    }

    public function testUserWhenPresent(): void
    {
        $update = BotAddedToChatUpdate::newFromData([
            'update_type' => 'bot_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'is_channel' => false,
        ]);
        $user = $update->getUser();

        self::assertTrue($update->isValid());
        self::assertNotNull($user);
        self::assertSame(200, $user->getUserId());
        self::assertSame($user, $update->getUser());
    }

    private function createUpdate(bool $isChannel): BotAddedToChatUpdate
    {
        $update = Update::newFromData([
            'update_type' => 'bot_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'is_channel' => $isChannel,
        ]);
        self::assertInstanceOf(BotAddedToChatUpdate::class, $update);

        return $update;
    }
}
