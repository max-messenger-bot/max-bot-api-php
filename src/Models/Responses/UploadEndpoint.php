<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Точка доступа, куда следует загружать ваши бинарные файлы.
 */
class UploadEndpoint extends BaseResponseModel
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
     * @return non-empty-string|null Видео- или аудио-токен для отправки сообщения (minLength: 1).
     */
    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    /**
     * @return non-empty-string URL для загрузки файла. Срок жизни ссылки не ограничен (minLength: 1).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
