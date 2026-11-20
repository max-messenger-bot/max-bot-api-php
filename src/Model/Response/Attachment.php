<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\AttachmentType;

/**
 * Общий класс, представляющий вложение сообщения.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
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
     * @return non-empty-string Тип вложения. Значения описаны в {@see AttachmentType}.
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * Создаёт вложение нужного типа по данным ответа API.
     *
     * @param array $data Данные вложения из ответа API.
     */
    public static function newFromData(array $data): static
    {
        if (static::class !== self::class) {
            return parent::newFromData($data);
        }

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

        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
