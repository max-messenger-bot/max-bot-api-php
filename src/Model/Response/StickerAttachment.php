<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вложение со стикером в сообщении.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class StickerAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     width: non-negative-int,
     *     height: non-negative-int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private StickerAttachmentPayload|false $payload = false;

    /**
     * @return non-negative-int Высота стикера.
     */
    public function getHeight(): int
    {
        return $this->data['height'];
    }

    /**
     * @return StickerAttachmentPayload Данные прикреплённого стикера.
     */
    public function getPayload(): StickerAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = StickerAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return non-negative-int Ширина стикера.
     */
    public function getWidth(): int
    {
        return $this->data['width'];
    }
}
