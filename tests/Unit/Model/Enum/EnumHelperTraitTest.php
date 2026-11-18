<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Enum;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\EnumHelperTrait;
use MaxMessenger\Bot\Model\Enum\UpdateType;

/**
 * Методы трейта {@see EnumHelperTrait}.
 */
final class EnumHelperTraitTest extends Unit
{
    public function testTryFromListKeepsOrderAndDuplicates(): void
    {
        self::assertSame(
            [ChatAdminPermission::Write, ChatAdminPermission::AddAdmins, ChatAdminPermission::Write],
            ChatAdminPermission::tryFromList(['write', 'add_admins', 'write']),
        );
    }

    public function testTryFromListReturnsListForStringKeys(): void
    {
        self::assertSame(
            [UpdateType::BotAdded, UpdateType::BotStarted],
            UpdateType::tryFromList(['first' => 'bot_added', 'second' => 'unknown', 'third' => 'bot_started']),
        );
    }

    public function testTryFromListSkipsUnknownValues(): void
    {
        self::assertSame(
            [UpdateType::MessageCreated, UpdateType::BotStarted],
            UpdateType::tryFromList(['message_created', 'unknown_update', '', 'bot_started']),
        );
    }

    public function testTryFromListWithEmptyList(): void
    {
        self::assertSame([], UpdateType::tryFromList([]));
    }

    public function testTryFromNullableListWithList(): void
    {
        self::assertSame(
            [ChatAdminPermission::ReadAllMessages],
            ChatAdminPermission::tryFromNullableList(['read_all_messages', 'unknown_permission']),
        );
        self::assertSame([], ChatAdminPermission::tryFromNullableList([]));
    }

    public function testTryFromNullableListWithNull(): void
    {
        self::assertNull(ChatAdminPermission::tryFromNullableList(null));
    }
}
