<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Полезная нагрузка запроса ShareAttachmentRequest.
 */
class ShareAttachmentPayload extends BaseResponseModel
{
    /**
     * @var array{
     *     url: non-empty-string,
     *     token?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string|null Токен вложения (minLength: 1).
     */
    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    /**
     * @return non-empty-string URL, прикрепленный к сообщению в качестве предпросмотра медиа (minLength: 1).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
