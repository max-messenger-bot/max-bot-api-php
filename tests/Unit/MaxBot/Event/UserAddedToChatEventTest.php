<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;

final class UserAddedToChatEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetInviterId(): void
    {
        self::assertSame(300, $this->createEvent(inviterId: 300)->getInviterId());
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

    public function testNoInviterId(): void
    {
        self::assertNull($this->createEvent()->getInviterId());
    }

    private function createEvent(?int $inviterId = null, bool $isChannel = false): UserAddedToChatEvent
    {
        $data = [
            'update_type' => 'user_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'is_channel' => $isChannel,
        ];
        if ($inviterId !== null) {
            $data['inviter_id'] = $inviterId;
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(UserAddedToChatEvent::class, $event);

        return $event;
    }
}
