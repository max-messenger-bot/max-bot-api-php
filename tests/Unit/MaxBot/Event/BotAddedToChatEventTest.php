<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;

final class BotAddedToChatEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetUser(): void
    {
        $user = $this->createEvent()->getUser();

        self::assertInstanceOf(User::class, $user);
        self::assertSame(200, $user->getUserId());
    }

    public function testGetUserId(): void
    {
        self::assertSame(200, $this->createEvent()->getUserId());
    }

    public function testIsChannelFalse(): void
    {
        self::assertFalse($this->createEvent(isChannel: false)->isChannel());
    }

    public function testIsChannelTrue(): void
    {
        self::assertTrue($this->createEvent(isChannel: true)->isChannel());
    }

    private function createEvent(bool $isChannel = false): BotAddedToChatEvent
    {
        $data = [
            'update_type' => 'bot_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'is_channel' => $isChannel,
        ];

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(BotAddedToChatEvent::class, $event);

        return $event;
    }
}
