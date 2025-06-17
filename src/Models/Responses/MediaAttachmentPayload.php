<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class MediaAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     id: int,
     *     url: non-empty-string,
     *     token: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    public function getId(): int
    {
        return $this->data['id'];
    }

    /**
     * @return non-empty-string Используйте `token`, если вы пытаетесь повторно использовать одно и то же вложение
     *     в другом сообщении (minLength: 1).
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @return non-empty-string
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
