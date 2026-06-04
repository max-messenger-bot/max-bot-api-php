<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;

final class ChatTitleChangedEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetTitle(): void
    {
        self::assertSame('Новое название', $this->createEvent()->getTitle());
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

    private function createEvent(): ChatTitleChangedEvent
    {
        $data = [
            'update_type' => 'chat_title_changed',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'title' => 'Новое название',
        ];

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(ChatTitleChangedEvent::class, $event);

        return $event;
    }
}
