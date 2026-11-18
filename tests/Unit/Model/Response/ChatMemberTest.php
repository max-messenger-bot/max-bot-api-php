<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\ChatMember;
use MaxMessenger\Bot\Model\Response\UserWithPhoto;

final class ChatMemberTest extends Unit
{
    public function testAdmin(): void
    {
        $member = ChatMember::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван',
            'is_bot' => false,
            'avatar_url' => 'https://example.test/avatar.jpg',
            'last_access_time' => 1_700_000_000_000,
            'is_owner' => false,
            'is_admin' => true,
            'join_time' => 1_699_000_000_000,
            'permissions' => ['write', 'unknown_permission', 'pin_message'],
            'alias' => 'Модератор',
        ]);

        self::assertInstanceOf(UserWithPhoto::class, $member);
        self::assertSame(200, $member->getUserId());
        self::assertSame('https://example.test/avatar.jpg', $member->getAvatarUrl());
        self::assertTrue($member->isAdmin());
        self::assertFalse($member->isOwner());
        self::assertSame(1_700_000_000_000, $member->getLastAccessTimeRaw());
        self::assertSame('2023-11-14 22:13:20', $member->getLastAccessTime()->format('Y-m-d H:i:s'));
        self::assertSame(1_699_000_000_000, $member->getJoinTimeRaw());
        self::assertSame('2023-11-03 08:26:40', $member->getJoinTime()->format('Y-m-d H:i:s'));
        self::assertSame(
            [ChatAdminPermission::Write, ChatAdminPermission::PinMessage],
            $member->getPermissions(),
        );
        self::assertSame(['write', 'unknown_permission', 'pin_message'], $member->getPermissionsRaw());
        self::assertSame('Модератор', $member->getAlias());
    }

    public function testHasPermission(): void
    {
        $member = ChatMember::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван',
            'is_bot' => false,
            'last_access_time' => 1_700_000_000_000,
            'is_owner' => false,
            'is_admin' => true,
            'join_time' => 1_699_000_000_000,
            'permissions' => ['write', 'unknown_permission', 'pin_message'],
        ]);
        self::assertTrue($member->hasPermission(ChatAdminPermission::Write));
        self::assertTrue($member->hasPermission(ChatAdminPermission::PinMessage));
        self::assertFalse($member->hasPermission(ChatAdminPermission::ReadAllMessages));

        $withoutPermissions = ChatMember::newFromData(['permissions' => []]);
        self::assertFalse($withoutPermissions->hasPermission(ChatAdminPermission::Write));

        $regularMember = ChatMember::newFromData([]);
        self::assertFalse($regularMember->hasPermission(ChatAdminPermission::Write));
    }

    public function testOwner(): void
    {
        $member = ChatMember::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван',
            'is_bot' => false,
            'last_access_time' => 1_700_000_000_000,
            'is_owner' => true,
            'is_admin' => true,
            'join_time' => 1_699_000_000_000,
            'permissions' => [],
        ]);

        self::assertTrue($member->isOwner());
        self::assertSame([], $member->getPermissions());
        self::assertSame([], $member->getPermissionsRaw());
    }

    public function testRegularMember(): void
    {
        $member = ChatMember::newFromData([
            'user_id' => 300,
            'first_name' => 'Пётр',
            'is_bot' => false,
            'last_access_time' => 1_700_000_000_000,
            'is_owner' => false,
            'is_admin' => false,
            'join_time' => 1_699_000_000_000,
        ]);

        self::assertFalse($member->isAdmin());
        self::assertFalse($member->isOwner());
        self::assertNull($member->getPermissions());
        self::assertNull($member->getPermissionsRaw());
        self::assertNull($member->getAlias());
    }
}
