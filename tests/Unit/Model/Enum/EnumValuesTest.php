<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Enum;

use BackedEnum;
use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\ChatStatus;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Enum\UploadType;

use function array_map;
use function sort;

/**
 * Значения перечислений совпадают со значениями `enum` и дискриминаторов в `schemas/schema.yaml`.
 */
final class EnumValuesTest extends Unit
{
    /**
     * @return iterable<string, array{class-string<BackedEnum>, list<string>}>
     */
    public static function enums(): iterable
    {
        yield 'AttachmentType' => [
            AttachmentType::class,
            [
                'audio',
                'contact',
                'file',
                'image',
                'inline_keyboard',
                'location',
                'share',
                'sticker',
                'video',
            ],
        ];
        yield 'ButtonType' => [
            ButtonType::class,
            [
                'callback',
                'clipboard',
                'link',
                'message',
                'open_app',
                'request_contact',
                'request_geo_location',
            ],
        ];
        yield 'ChatAdminPermission' => [
            ChatAdminPermission::class,
            [
                'add_admins',
                'add_remove_members',
                'can_call',
                'change_chat_info',
                'delete',
                'edit',
                'edit_link',
                'pin_message',
                'read_all_messages',
                'view_stats',
                'write',
            ],
        ];
        yield 'ChatStatus' => [ChatStatus::class, ['active', 'closed', 'left', 'removed', 'suspended']];
        yield 'ChatType' => [ChatType::class, ['channel', 'chat', 'dialog']];
        yield 'MarkupElementType' => [
            MarkupElementType::class,
            [
                'emphasized',
                'heading',
                'highlighted',
                'link',
                'monospaced',
                'quote',
                'strikethrough',
                'strong',
                'underline',
                'user_mention',
            ],
        ];
        yield 'MessageLinkType' => [MessageLinkType::class, ['forward', 'reply']];
        yield 'SenderAction' => [
            SenderAction::class,
            [
                'mark_seen',
                'sending_audio',
                'sending_file',
                'sending_photo',
                'sending_video',
                'typing_on',
            ],
        ];
        yield 'TextFormat' => [TextFormat::class, ['html', 'markdown']];
        yield 'UpdateType' => [
            UpdateType::class,
            [
                'bot_added',
                'bot_admin_permissions_changed',
                'bot_removed',
                'bot_started',
                'bot_stopped',
                'chat_title_changed',
                'comment_created',
                'comment_edited',
                'comment_removed',
                'dialog_cleared',
                'dialog_muted',
                'dialog_removed',
                'dialog_unmuted',
                'message_callback',
                'message_created',
                'message_edited',
                'message_removed',
                'user_added',
                'user_removed',
            ],
        ];
        yield 'UploadType' => [UploadType::class, ['audio', 'file', 'image', 'video']];
    }

    /**
     * @param class-string<BackedEnum> $enum
     * @param list<string> $expected
     * @dataProvider enums
     */
    public function testValues(string $enum, array $expected): void
    {
        $values = array_map(static fn(BackedEnum $case): int|string => $case->value, $enum::cases());
        sort($values);

        self::assertSame($expected, $values);
    }
}
