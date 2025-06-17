<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Объект с общей информацией о пользователе или боте, дополнительно содержит URL аватара и описание.
 *
 * @link https://dev.max.ru/docs-api/objects/UserWithPhoto
 */
class UserWithPhoto extends User
{
    /**
     * @var array{
     *     description?: non-empty-string,
     *     avatar_url?: non-empty-string,
     *     full_avatar_url?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string|null URL аватара пользователя или бота в уменьшенном размере (minLength: 1).
     */
    public function getAvatarUrl(): ?string
    {
        return $this->data['avatar_url'] ?? null;
    }

    /**
     * @return non-empty-string|null Описание пользователя или бота (minLength: 1, maxLength: 16000).
     *     В случае с пользователем может принимать значение `null`, если описание не заполнено.
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string|null URL аватара пользователя или бота в полном размере (minLength: 1).
     */
    public function getFullAvatarUrl(): ?string
    {
        return $this->data['full_avatar_url'] ?? null;
    }
}
