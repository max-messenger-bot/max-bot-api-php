<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ChatStatus;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Response\Chat;
use MaxMessenger\Bot\Model\Response\Image;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\UserWithPhoto;

final class ChatTest extends Unit
{
    public function testDialog(): void
    {
        $chat = Chat::newFromData([
            'chat_id' => 100,
            'type' => 'dialog',
            'status' => 'active',
            'last_event_time' => 1_700_000_000_000,
            'participants_count' => 2,
            'is_public' => false,
            'dialog_with_user' => [
                'user_id' => 200,
                'first_name' => 'Иван',
                'is_bot' => false,
                'avatar_url' => 'https://example.test/avatar.jpg',
            ],
            'chat_message_id' => 'mid.button',
        ]);

        self::assertSame(ChatType::Dialog, $chat->getType());
        self::assertSame(2, $chat->getParticipantsCount());
        self::assertFalse($chat->isPublic());
        self::assertSame('mid.button', $chat->getChatMessageId());

        $user = $chat->getDialogWithUser();
        self::assertInstanceOf(UserWithPhoto::class, $user);
        self::assertSame(200, $user->getUserId());
        self::assertSame('https://example.test/avatar.jpg', $user->getAvatarUrl());
        self::assertSame($user, $chat->getDialogWithUser());
    }

    public function testGroupChat(): void
    {
        $chat = Chat::newFromData([
            'chat_id' => -100,
            'type' => 'chat',
            'status' => 'active',
            'title' => 'Команда',
            'description' => 'Рабочий чат',
            'icon' => ['url' => 'https://example.test/icon.png'],
            'last_event_time' => 1_700_000_000_000,
            'participants_count' => 3,
            'owner_id' => 200,
            'participants' => ['200' => 1_700_000_000_000, '300' => 1_699_999_000_000],
            'is_public' => true,
            'link' => 'https://max.ru/join/team',
            'messages_count' => 42,
            'pinned_message' => [
                'recipient' => ['chat_id' => -100, 'chat_type' => 'chat'],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.pinned', 'seq' => 1, 'text' => 'Закреплено'],
            ],
        ]);

        self::assertSame(-100, $chat->getChatId());
        self::assertSame(ChatType::Chat, $chat->getType());
        self::assertSame('chat', $chat->getTypeRaw());
        self::assertSame(ChatStatus::Active, $chat->getStatus());
        self::assertSame('active', $chat->getStatusRaw());
        self::assertSame('Команда', $chat->getTitle());
        self::assertSame('Рабочий чат', $chat->getDescription());
        self::assertSame(1_700_000_000_000, $chat->getLastEventTimeRaw());
        self::assertSame('2023-11-14 22:13:20', $chat->getLastEventTime()->format('Y-m-d H:i:s'));
        self::assertSame(3, $chat->getParticipantsCount());
        self::assertSame(200, $chat->getOwnerId());
        self::assertSame([200 => 1_700_000_000_000, 300 => 1_699_999_000_000], $chat->getParticipants());
        self::assertTrue($chat->isPublic());
        self::assertSame('https://max.ru/join/team', $chat->getLink());
        self::assertSame(42, $chat->getMessagesCount());
        self::assertNull($chat->getDialogWithUser());
        self::assertNull($chat->getChatMessageId());

        $icon = $chat->getIcon();
        self::assertInstanceOf(Image::class, $icon);
        self::assertSame('https://example.test/icon.png', $icon->getUrl());
        self::assertSame($icon, $chat->getIcon());

        $pinned = $chat->getPinnedMessage();
        self::assertInstanceOf(Message::class, $pinned);
        self::assertSame('Закреплено', $pinned->getText());
        self::assertSame($pinned, $chat->getPinnedMessage());
    }

    public function testMinimalChat(): void
    {
        $chat = Chat::newFromData([
            'chat_id' => -100,
            'type' => 'channel',
            'status' => 'left',
            'last_event_time' => 1_700_000_000_000,
            'participants_count' => 10,
            'is_public' => false,
        ]);

        self::assertSame(ChatType::Channel, $chat->getType());
        self::assertSame(ChatStatus::Left, $chat->getStatus());
        self::assertNull($chat->getTitle());
        self::assertNull($chat->getDescription());
        self::assertNull($chat->getIcon());
        self::assertNull($chat->getOwnerId());
        self::assertNull($chat->getParticipants());
        self::assertNull($chat->getLink());
        self::assertNull($chat->getMessagesCount());
        self::assertNull($chat->getPinnedMessage());
        self::assertNull($chat->getDialogWithUser());
        self::assertNull($chat->getChatMessageId());
    }

    public function testUnknownTypeAndStatus(): void
    {
        $chat = Chat::newFromData([
            'chat_id' => -100,
            'type' => 'unknown_type',
            'status' => 'unknown_status',
            'last_event_time' => 1_700_000_000_000,
            'participants_count' => 1,
            'is_public' => false,
        ]);

        self::assertNull($chat->getType());
        self::assertSame('unknown_type', $chat->getTypeRaw());
        self::assertNull($chat->getStatus());
        self::assertSame('unknown_status', $chat->getStatusRaw());
    }
}
