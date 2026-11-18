<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\BotAdminPermissionsChangedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class BotAdminPermissionsChangedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate([
            'is_channel' => true,
            'is_admin' => true,
            'permissions' => ['read_all_messages', 'future_permission', 'write'],
        ]);

        self::assertSame(100, $update->getChatId());
        self::assertSame(200, $update->getUserId());
        self::assertSame(789, $update->getBotId());
        self::assertTrue($update->isChannel());
        self::assertTrue($update->isAdmin());
        self::assertSame(
            [ChatAdminPermission::ReadAllMessages, ChatAdminPermission::Write],
            $update->getPermissions(),
        );
        self::assertSame(['read_all_messages', 'future_permission', 'write'], $update->getPermissionsRaw());
    }

    public function testWithoutPermissions(): void
    {
        $update = $this->createUpdate(['is_channel' => false, 'is_admin' => false]);

        self::assertFalse($update->isChannel());
        self::assertFalse($update->isAdmin());
        self::assertNull($update->getPermissions());
        self::assertNull($update->getPermissionsRaw());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdate(array $data): BotAdminPermissionsChangedUpdate
    {
        $update = Update::newFromData($data + [
            'update_type' => 'bot_admin_permissions_changed',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user_id' => 200,
            'bot_id' => 789,
        ]);
        self::assertInstanceOf(BotAdminPermissionsChangedUpdate::class, $update);

        return $update;
    }
}
