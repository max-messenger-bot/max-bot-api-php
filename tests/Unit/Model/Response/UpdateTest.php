<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\BotAdminPermissionsChangedUpdate;
use MaxMessenger\Bot\Model\Response\BotRemovedFromChatUpdate;
use MaxMessenger\Bot\Model\Response\BotStartedUpdate;
use MaxMessenger\Bot\Model\Response\BotStoppedUpdate;
use MaxMessenger\Bot\Model\Response\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Model\Response\CommentCreatedUpdate;
use MaxMessenger\Bot\Model\Response\CommentEditedUpdate;
use MaxMessenger\Bot\Model\Response\CommentRemovedUpdate;
use MaxMessenger\Bot\Model\Response\DialogClearedUpdate;
use MaxMessenger\Bot\Model\Response\DialogMutedUpdate;
use MaxMessenger\Bot\Model\Response\DialogRemovedUpdate;
use MaxMessenger\Bot\Model\Response\DialogUnmutedUpdate;
use MaxMessenger\Bot\Model\Response\MessageCallbackUpdate;
use MaxMessenger\Bot\Model\Response\MessageCreatedUpdate;
use MaxMessenger\Bot\Model\Response\MessageEditedUpdate;
use MaxMessenger\Bot\Model\Response\MessageRemovedUpdate;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\UserAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\UserRemovedFromChatUpdate;

final class UpdateTest extends Unit
{
    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function missingFields(): iterable
    {
        yield 'нет update_type' => [['timestamp' => 1_700_000_000_000], 'update_type'];
        yield 'нет timestamp' => [['update_type' => 'bot_started'], 'timestamp'];
        yield 'нет обоих полей' => [[], 'update_type'];
    }

    public function testIsValidAllowsMissingOptionalProperties(): void
    {
        $update = Update::newFromData(['update_type' => 'future_update', 'timestamp' => 1_700_000_000_000]);

        self::assertSame([], $update->getRequiredProperties());
        self::assertTrue($update->isValid());
    }

    /**
     * @param non-empty-string $updateType
     * @param class-string<Update> $class
     * @dataProvider updateTypes
     */
    public function testIsValidChecksRequiredPropertyKeys(string $updateType, string $class, UpdateType $type): void
    {
        $data = [
            'update_type' => $updateType,
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 0,
            'user' => null,
            'user_id' => 0,
            'bot_id' => 0,
            'is_channel' => false,
            'is_admin' => false,
            'title' => '',
            'message' => null,
            'message_id' => '',
            'post_id' => '',
            'muted_until' => 0,
            'callback' => null,
            'extra' => true,
        ];
        $update = Update::newFromData($data);

        self::assertSame($class, $update::class);
        self::assertSame($type, $update->getUpdateType());
        self::assertTrue($update->isValid());
        self::assertNotContains('update_type', $update->getRequiredProperties());
        self::assertNotContains('timestamp', $update->getRequiredProperties());

        foreach ($update->getRequiredProperties() as $property) {
            $incompleteData = $data;
            unset($incompleteData[$property]);

            self::assertFalse(Update::newFromData($incompleteData)->isValid(), $property);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @dataProvider missingFields
     */
    public function testNewFromDataRequiresFields(array $data, string $field): void
    {
        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "' . $field . '" field cannot be null.');

        Update::newFromData($data);
    }

    /**
     * @param non-empty-string $updateType
     * @param class-string<Update> $class
     * @dataProvider updateTypes
     */
    public function testNewFromDataSelectsClassByUpdateType(string $updateType, string $class, UpdateType $type): void
    {
        $update = Update::newFromData(['update_type' => $updateType, 'timestamp' => 1_700_000_000_000]);

        self::assertSame($class, $update::class);
        self::assertSame($type, $update->getUpdateType());
        self::assertSame($updateType, $update->getUpdateTypeRaw());
    }

    public function testSubclassNewFromDataDoesNotSelectClass(): void
    {
        $update = BotStartedUpdate::newFromData(['update_type' => 'bot_stopped', 'timestamp' => 1_700_000_000_000]);

        self::assertSame(BotStartedUpdate::class, $update::class);
        self::assertSame(UpdateType::BotStopped, $update->getUpdateType());
    }

    public function testSubclassNewFromDataRequiresFields(): void
    {
        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "timestamp" field cannot be null.');

        BotStartedUpdate::newFromData(['update_type' => 'bot_started']);
    }

    public function testTimestamp(): void
    {
        $update = Update::newFromData(['update_type' => 'bot_started', 'timestamp' => 1_700_000_000_123]);

        self::assertSame(1_700_000_000_123, $update->getTimestampRaw());
        self::assertSame('2023-11-14 22:13:20.123 +00:00', $update->getTimestamp()->format('Y-m-d H:i:s.v P'));
    }

    public function testUnknownUpdateType(): void
    {
        $update = Update::newFromData(['update_type' => 'future_update', 'timestamp' => 1_700_000_000_000]);

        self::assertSame(Update::class, $update::class);
        self::assertNull($update->getUpdateType());
        self::assertSame('future_update', $update->getUpdateTypeRaw());
        self::assertSame(1_700_000_000_000, $update->getTimestampRaw());
    }

    /**
     * @return iterable<string, array{non-empty-string, class-string<Update>, UpdateType}>
     */
    public static function updateTypes(): iterable
    {
        yield 'bot_added' => ['bot_added', BotAddedToChatUpdate::class, UpdateType::BotAdded];
        yield 'bot_admin_permissions_changed' => [
            'bot_admin_permissions_changed',
            BotAdminPermissionsChangedUpdate::class,
            UpdateType::BotAdminPermissionsChanged,
        ];
        yield 'bot_removed' => ['bot_removed', BotRemovedFromChatUpdate::class, UpdateType::BotRemoved];
        yield 'bot_started' => ['bot_started', BotStartedUpdate::class, UpdateType::BotStarted];
        yield 'bot_stopped' => ['bot_stopped', BotStoppedUpdate::class, UpdateType::BotStopped];
        yield 'chat_title_changed' => [
            'chat_title_changed',
            ChatTitleChangedUpdate::class,
            UpdateType::ChatTitleChanged,
        ];
        yield 'comment_created' => ['comment_created', CommentCreatedUpdate::class, UpdateType::CommentCreated];
        yield 'comment_edited' => ['comment_edited', CommentEditedUpdate::class, UpdateType::CommentEdited];
        yield 'comment_removed' => ['comment_removed', CommentRemovedUpdate::class, UpdateType::CommentRemoved];
        yield 'dialog_cleared' => ['dialog_cleared', DialogClearedUpdate::class, UpdateType::DialogCleared];
        yield 'dialog_muted' => ['dialog_muted', DialogMutedUpdate::class, UpdateType::DialogMuted];
        yield 'dialog_removed' => ['dialog_removed', DialogRemovedUpdate::class, UpdateType::DialogRemoved];
        yield 'dialog_unmuted' => ['dialog_unmuted', DialogUnmutedUpdate::class, UpdateType::DialogUnmuted];
        yield 'message_callback' => ['message_callback', MessageCallbackUpdate::class, UpdateType::MessageCallback];
        yield 'message_created' => ['message_created', MessageCreatedUpdate::class, UpdateType::MessageCreated];
        yield 'message_edited' => ['message_edited', MessageEditedUpdate::class, UpdateType::MessageEdited];
        yield 'message_removed' => ['message_removed', MessageRemovedUpdate::class, UpdateType::MessageRemoved];
        yield 'user_added' => ['user_added', UserAddedToChatUpdate::class, UpdateType::UserAdded];
        yield 'user_removed' => ['user_removed', UserRemovedFromChatUpdate::class, UpdateType::UserRemoved];
    }
}
