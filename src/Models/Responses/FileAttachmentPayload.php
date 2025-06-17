<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class FileAttachmentPayload extends AttachmentPayload
{
    /**
     * @var array{
     *     token: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Используйте `token`, если вы пытаетесь повторно использовать одно и то же вложение
     *     в другом сообщении (minLength: 1).
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }
}
