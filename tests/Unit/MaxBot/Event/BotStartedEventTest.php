<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;

final class BotStartedEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetPayload(): void
    {
        self::assertSame('start-payload', $this->createEvent(payload: 'start-payload')->getPayload());
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

    public function testNoPayload(): void
    {
        self::assertNull($this->createEvent()->getPayload());
    }

    public function testNoUserLocale(): void
    {
        self::assertNull($this->createEvent()->getUserLocale());
    }

    private function createEvent(?string $payload = null, ?string $userLocale = null): BotStartedEvent
    {
        $data = [
            'update_type' => 'bot_started',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ];
        if ($payload !== null) {
            $data['payload'] = $payload;
        }
        if ($userLocale !== null) {
            $data['user_locale'] = $userLocale;
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(BotStartedEvent::class, $event);

        return $event;
    }
}
