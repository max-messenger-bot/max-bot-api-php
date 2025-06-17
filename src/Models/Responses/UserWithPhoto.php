<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Объект с общей информацией о пользователе или боте, дополнительно содержит URL аватара и описание.
 *
 * @link https://dev.max.ru/docs-api/objects/UserWithPhoto
 * @api
 */
class UserWithPhoto extends User
{
    /**
     * @var array{
     *     description?: string|null,
     *     avatar_url?: string,
     *     full_avatar_url?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null URL аватара пользователя или бота в уменьшенном размере.
     * @api
     */
    public function getAvatarUrl(): ?string
    {
        return $this->data['avatar_url'] ?? null;
    }

    /**
     * @return string|null Описание пользователя или бота (maxLength: 16000).
     *     В случае с пользователем может принимать значение `null`, если описание не заполнено.
     * @api
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return string|null URL аватара пользователя или бота в полном размере.
     * @api
     */
    public function getFullAvatarUrl(): ?string
    {
        return $this->data['full_avatar_url'] ?? null;
    }
}
