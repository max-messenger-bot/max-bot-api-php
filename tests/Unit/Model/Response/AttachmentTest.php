<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\Attachment;
use MaxMessenger\Bot\Model\Response\AudioAttachment;
use MaxMessenger\Bot\Model\Response\ContactAttachment;
use MaxMessenger\Bot\Model\Response\FileAttachment;
use MaxMessenger\Bot\Model\Response\InlineKeyboardAttachment;
use MaxMessenger\Bot\Model\Response\LocationAttachment;
use MaxMessenger\Bot\Model\Response\PhotoAttachment;
use MaxMessenger\Bot\Model\Response\ShareAttachment;
use MaxMessenger\Bot\Model\Response\StickerAttachment;
use MaxMessenger\Bot\Model\Response\VideoAttachment;

final class AttachmentTest extends Unit
{
    /**
     * @return iterable<string, array{array<string, mixed>, class-string<Attachment>, AttachmentType}>
     */
    public static function attachments(): iterable
    {
        $media = ['url' => 'https://example.test/media', 'id' => 1, 'token' => 'media-token'];

        yield 'audio' => [['type' => 'audio', 'payload' => $media], AudioAttachment::class, AttachmentType::Audio];
        yield 'contact' => [['type' => 'contact', 'payload' => []], ContactAttachment::class, AttachmentType::Contact];
        yield 'file' => [
            [
                'type' => 'file',
                'payload' => ['url' => 'https://example.test/file', 'token' => 'file-token'],
                'filename' => 'report.pdf',
                'size' => 1024,
            ],
            FileAttachment::class,
            AttachmentType::File,
        ];
        yield 'image' => [
            [
                'type' => 'image',
                'payload' => ['photo_id' => 1, 'url' => 'https://example.test/photo', 'token' => 'photo-token'],
            ],
            PhotoAttachment::class,
            AttachmentType::Image,
        ];
        yield 'inline_keyboard' => [
            ['type' => 'inline_keyboard', 'payload' => ['buttons' => [[['type' => 'message', 'text' => 'Да']]]]],
            InlineKeyboardAttachment::class,
            AttachmentType::InlineKeyboard,
        ];
        yield 'location' => [
            ['type' => 'location', 'latitude' => 55.75, 'longitude' => 37.62],
            LocationAttachment::class,
            AttachmentType::Location,
        ];
        yield 'share' => [
            ['type' => 'share', 'payload' => ['url' => 'https://example.test']],
            ShareAttachment::class,
            AttachmentType::Share,
        ];
        yield 'sticker' => [
            [
                'type' => 'sticker',
                'payload' => ['url' => 'https://example.test/sticker', 'code' => 'sticker-code'],
                'width' => 128,
                'height' => 128,
            ],
            StickerAttachment::class,
            AttachmentType::Sticker,
        ];
        yield 'video' => [['type' => 'video', 'payload' => $media], VideoAttachment::class, AttachmentType::Video];
    }

    /**
     * @param array<string, mixed> $data
     * @param class-string<Attachment> $class
     * @dataProvider attachments
     */
    public function testNewFromDataCreatesAttachmentByType(array $data, string $class, AttachmentType $type): void
    {
        $attachment = Attachment::newFromData($data);

        self::assertInstanceOf($class, $attachment);
        self::assertSame($type, $attachment->getType());
        self::assertSame($type->value, $attachment->getTypeRaw());
    }

    public function testNewFromDataForUnknownType(): void
    {
        $attachment = Attachment::newFromData(['type' => 'hologram']);

        self::assertSame(Attachment::class, $attachment::class);
        self::assertNull($attachment->getType());
        self::assertSame('hologram', $attachment->getTypeRaw());
    }

    public function testNewListFromData(): void
    {
        $attachments = Attachment::newListFromData([
            ['type' => 'location', 'latitude' => 55.75, 'longitude' => 37.62],
            ['type' => 'hologram'],
        ]);

        self::assertCount(2, $attachments);
        self::assertInstanceOf(LocationAttachment::class, $attachments[0]);
        self::assertSame(Attachment::class, $attachments[1]::class);
    }
}
