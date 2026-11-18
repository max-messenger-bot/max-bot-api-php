<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\Update;

final class BotAdminPermissionsChangedEventTest extends Unit
{
    public function testGetBotId(): void
    {
        self::assertSame(789, $this->createEvent()->getBotId());
    }

    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetPermissions(): void
    {
        self::assertSame(
            [ChatAdminPermission::ReadAllMessages, ChatAdminPermission::Write],
            $this->createEvent()->getPermissions(),
        );
    }

    public function testGetPermissionsRaw(): void
    {
        self::assertSame(['read_all_messages', 'write'], $this->createEvent()->getPermissionsRaw());
    }

    public function testGetPermissionsWithoutPermissions(): void
    {
        $event = $this->createEvent(permissions: null);

        self::assertNull($event->getPermissions());
        self::assertNull($event->getPermissionsRaw());
    }

    public function testGetUser(): void
    {
        self::assertNull($this->createEvent()->getUser());
    }

    public function testGetUserId(): void
    {
        self::assertSame(200, $this->createEvent()->getUserId());
    }

    public function testIsAdmin(): void
    {
        self::assertTrue($this->createEvent(isAdmin: true)->isAdmin());
        self::assertFalse($this->createEvent(isAdmin: false)->isAdmin());
    }

    public function testIsChannel(): void
    {
        self::assertTrue($this->createEvent(isChannel: true)->isChannel());
        self::assertFalse($this->createEvent(isChannel: false)->isChannel());
    }

    /**
     * @param list<string>|null $permissions
     */
    private function createEvent(
        bool $isChannel = false,
        bool $isAdmin = true,
        ?array $permissions = ['read_all_messages', 'write'],
    ): BotAdminPermissionsChangedEvent {
        $data = [
            'update_type' => 'bot_admin_permissions_changed',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user_id' => 200,
            'bot_id' => 789,
            'is_channel' => $isChannel,
            'is_admin' => $isAdmin,
        ];

        if ($permissions !== null) {
            $data['permissions'] = $permissions;
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(BotAdminPermissionsChangedEvent::class, $event);

        return $event;
    }
}
