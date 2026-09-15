<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Данные прикреплённого к сообщению предпросмотра медиавложения.
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
     * @return non-empty-string|null Токен вложения.
     */
    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    /**
     * @return non-empty-string URL, прикреплённый к сообщению для предпросмотра медиавложения.
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
