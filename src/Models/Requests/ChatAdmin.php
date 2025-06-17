<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ChatAdminPermission;

/**
 * Администратор чата с правами доступа.
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
     *     alias?: string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param int $userId Идентификатор пользователя-участника чата, который назначается администратором.
     * @param ChatAdminPermission[] $permissions Перечень прав доступа пользователя.
     * @param string|null $alias Заголовок, который будет показан на клиенте.
     *     Если пользователь администратор или владелец и ему не установлено это название, то поле не передаётся,
     *     клиенты на своей стороне подменят на "владелец" или "админ".
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
     * @api
     */
    public function issetAlias(): bool
    {
        return array_key_exists('alias', $this->data);
    }

    /**
     * @param int|null $userId Идентификатор пользователя-участника чата, который назначается администратором.
     * @psalm-param int $userId
     * @param ChatAdminPermission[]|null $permissions Перечень прав доступа пользователя.
     * @psalm-param ChatAdminPermission[] $permissions
     * @param string|null $alias Заголовок, который будет показан на клиенте.
     *     Если пользователь администратор или владелец и ему не установлено это название, то поле не передаётся,
     *     клиенты на своей стороне подменят на "владелец" или "админ".
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
     * @param string $alias Заголовок, который будет показан на клиенте.
     *     Если пользователь администратор или владелец и ему не установлено это название, то поле не передаётся,
     *     клиенты на своей стороне подменят на "владелец" или "админ".
     * @return $this
     * @api
     */
    public function setAlias(string $alias): static
    {
        $this->data['alias'] = $alias;

        return $this;
    }

    /**
     * @param ChatAdminPermission[] $permissions Перечень прав доступа пользователя.
     * @return $this
     * @api
     */
    public function setPermissions(array $permissions): static
    {
        $this->data['permissions'] = array_values(array_unique($permissions));

        return $this;
    }

    /**
     * @param int $userId Идентификатор пользователя-участника чата, который назначается администратором.
     * @return $this
     * @api
     */
    public function setUserId(int $userId): static
    {
        $this->data['user_id'] = $userId;

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetAlias(): static
    {
        unset($this->data['alias']);

        return $this;
    }
}
