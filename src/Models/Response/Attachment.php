<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use MaxMessenger\Bot\Models\Enums\AttachmentType;

/**
 * Generic schema representing message attachment.
 *
 * @api
 */
readonly class Attachment extends BaseResponseModel
{
    /**
     * @var array{
     *     type: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return AttachmentType Type of attachment.
     * @api
     */
    public function getType(): AttachmentType
    {
        return AttachmentType::from($this->data['type']);
    }

    /**
     * @return string Type of attachment.
     * @api
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * Creates an object using a map.
     */
    public static function newFromData(array $data): static
    {
        if (static::class !== self::class) {
            parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'audio' => AudioAttachment::class,
            'contact' => ContactAttachment::class,
            'data' => DataAttachment::class,
            'file' => FileAttachment::class,
            'image' => PhotoAttachment::class,
            'inline_keyboard' => InlineKeyboardAttachment::class,
            'location' => LocationAttachment::class,
            'reply_keyboard' => ReplyKeyboardAttachment::class,
            'share' => ShareAttachment::class,
            'sticker' => StickerAttachment::class,
            'video' => VideoAttachment::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? self::class;

        return $className::newFromData($data);
    }
}
