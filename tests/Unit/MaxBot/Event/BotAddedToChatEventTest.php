<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\Model\Response\Update;

final class BotAddedToChatEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetUser(): void
    {
        self::assertNull($this->createEvent()->getUser());
    }

    public function testGetUserId(): void
    {
        self::assertNull($this->createEvent()->getUserId());
    }

    public function testIsChannelFalse(): void
    {
        self::assertFalse($this->createEvent(isChannel: false)->isChannel());
    }

    public function testIsChannelTrue(): void
    {
        self::assertTrue($this->createEvent(isChannel: true)->isChannel());
    }

    public function testUserWhenPresent(): void
    {
        $event = $this->createEvent(withUser: true);
        $user = $event->getUser();

        self::assertNotNull($user);
        self::assertSame(200, $user->getUserId());
        self::assertSame(200, $event->getUserId());
    }

    private function createEvent(bool $isChannel = false, bool $withUser = false): BotAddedToChatEvent
    {
        $data = [
            'update_type' => 'bot_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'is_channel' => $isChannel,
        ];
        if ($withUser) {
            $data['user'] = ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false];
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(BotAddedToChatEvent::class, $event);

        return $event;
    }
}
