<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Данные прикреплённого к сообщению стикера.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class StickerAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     code: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Идентификатор стикера.
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }
}
