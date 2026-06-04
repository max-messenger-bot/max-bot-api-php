<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStoppedEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;

final class BotStoppedEventTest extends Unit
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

    public function testGetUserLocale(): void
    {
        self::assertSame('ru-RU', $this->createEvent(userLocale: 'ru-RU')->getUserLocale());
    }

    public function testNoUserLocale(): void
    {
        self::assertNull($this->createEvent()->getUserLocale());
    }

    private function createEvent(?string $userLocale = null): BotStoppedEvent
    {
        $data = [
            'update_type' => 'bot_stopped',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ];
        if ($userLocale !== null) {
            $data['user_locale'] = $userLocale;
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(BotStoppedEvent::class, $event);

        return $event;
    }
}
