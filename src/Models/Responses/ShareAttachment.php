<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class ShareAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array,
     *     title?: non-empty-string,
     *     description?: non-empty-string,
     *     image_url?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private ShareAttachmentPayload|false $payload = false;

    /**
     * @return non-empty-string|null Описание предпросмотра ссылки (minLength: 1).
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string|null Изображение предпросмотра ссылки (minLength: 1).
     */
    public function getImageUrl(): ?string
    {
        return $this->data['image_url'] ?? null;
    }

    /**
     * @return ShareAttachmentPayload
     */
    public function getPayload(): ShareAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = ShareAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }

    /**
     * @return non-empty-string|null Заголовок предпросмотра ссылки (minLength: 1).
     */
    public function getTitle(): ?string
    {
        return $this->data['title'] ?? null;
    }
}
