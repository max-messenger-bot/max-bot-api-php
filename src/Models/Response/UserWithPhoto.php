<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * @api
 */
readonly class UserWithPhoto extends User
{
    /**
     * @var array{
     *     description?: string|null,
     *     avatar_url?: string,
     *     full_avatar_url?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string|null URL of avatar.
     * @api
     */
    public function getAvatarUrl(): ?string
    {
        return $this->data['avatar_url'] ?? null;
    }

    /**
     * @return string|null User description. Can be `null` if user did not fill it out (maxLength: 16000).
     * @api
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return string|null URL of avatar of a bigger size.
     * @api
     */
    public function getFullAvatarUrl(): ?string
    {
        return $this->data['full_avatar_url'] ?? null;
    }
}
