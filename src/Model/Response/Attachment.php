<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\AttachmentType;

/**
 * Общий класс, представляющий вложение сообщения.
 */
class Attachment extends BaseResponseModel
{
    /**
     * @var array{
     *     type: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return AttachmentType|null Тип вложения.
     */
    public function getType(): ?AttachmentType
    {
        return AttachmentType::tryFrom($this->data['type']);
    }

    /**
     * @return non-empty-string Тип вложения.
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
            /** @psalm-var static Psalm bug */
            return parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'audio' => AudioAttachment::class,
            'contact' => ContactAttachment::class,
            'file' => FileAttachment::class,
            'image' => PhotoAttachment::class,
            'inline_keyboard' => InlineKeyboardAttachment::class,
            'location' => LocationAttachment::class,
            'share' => ShareAttachment::class,
            'sticker' => StickerAttachment::class,
            'video' => VideoAttachment::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? null;

        /** @psalm-var static Psalm bug */
        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
