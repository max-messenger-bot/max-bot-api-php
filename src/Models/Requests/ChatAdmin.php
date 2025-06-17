<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ChatAdminPermission;

/**
 * Administrator id with permissions.
 *
 * @api
 */
final class ChatAdmin extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     user_id: int,
     *     permissions: list<ChatAdminPermission>,
     *     alias?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param int $userId Administrator user identifier.
     * @param ChatAdminPermission[] $permissions Administrator permissions.
     * @param string|null $alias Alias of the admin in chat.
     * @api
     */
    public function __construct(
        int $userId,
        array $permissions,
        ?string $alias = null,
    ) {
        $this->setUserId($userId);
        $this->setPermissions($permissions);
        if ($alias !== null) {
            $this->setAlias($alias);
        }
    }

    /**
     * @api
     */
    public function getAlias(): ?string
    {
        return $this->data['alias'] ?? null;
    }

    /**
     * @return list<ChatAdminPermission>
     * @api
     */
    public function getPermissions(): array
    {
        return $this->data['permissions'];
    }

    /**
     * @api
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }

    /**
     * @param int|null $userId Administrator user identifier.
     * @psalm-param int $userId
     * @param ChatAdminPermission[]|null $permissions Administrator permissions.
     * @psalm-param ChatAdminPermission[] $permissions
     * @param string|null $alias Alias of the admin in chat. By default, null.
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(
        ?int $userId = null,
        ?array $permissions = null,
        ?string $alias = null,
    ): static {
        static::validateNotNull('userId', $userId);
        static::validateNotNull('permissions', $permissions);

        return new static($userId, $permissions, $alias);
    }

    /**
     * Alias of the admin in chat.
     *
     * @param string|null $alias
     * @return $this
     * @api
     */
    public function setAlias(?string $alias = null): static
    {
        $this->data['alias'] = $alias;

        return $this;
    }

    /**
     * Administrator permissions.
     *
     * @param ChatAdminPermission[] $permissions
     * @return $this
     * @api
     */
    public function setPermissions(array $permissions): static
    {
        $this->data['permissions'] = array_values(array_unique($permissions));

        return $this;
    }

    /**
     * Administrator user identifier.
     *
     * @param int $userId Administrator user identifier.
     * @return $this
     * @api
     */
    public function setUserId(int $userId): static
    {
        $this->data['user_id'] = $userId;

        return $this;
    }
}
