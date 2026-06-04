<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use DateTimeImmutable;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogMutedEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;

final class DialogMutedEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetMutedUntil(): void
    {
        self::assertInstanceOf(DateTimeImmutable::class, $this->createEvent()->getMutedUntil());
    }

    public function testGetMutedUntilRaw(): void
    {
        self::assertSame(1_700_000_500, $this->createEvent()->getMutedUntilRaw());
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
        self::assertSame('ru-RU', $this->createEvent()->getUserLocale());
    }

    public function testNoUserLocale(): void
    {
        self::assertNull($this->createEvent(userLocale: null)->getUserLocale());
    }

    private function createEvent(?string $userLocale = 'ru-RU'): DialogMutedEvent
    {
        $data = [
            'update_type' => 'dialog_muted',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'muted_until' => 1_700_000_500,
        ];
        if ($userLocale !== null) {
            $data['user_locale'] = $userLocale;
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(DialogMutedEvent::class, $event);

        return $event;
    }
}
