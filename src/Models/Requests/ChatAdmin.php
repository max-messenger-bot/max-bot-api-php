<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ChatAdminPermission;

use function array_key_exists;

/**
 * Администратор чата с правами доступа.
 */
final class ChatAdmin extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     user_id: int,
     *     permissions: list<ChatAdminPermission>,
     *     alias?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param int|null $userId Идентификатор пользователя-участника чата, который назначается администратором.
     * @param ChatAdminPermission[]|null $permissions Перечень прав доступа пользователя.
     * @param non-empty-string|null $alias Заголовок, который будет показан на клиенте (minLength: 1).
     *     Если пользователь администратор или владелец и ему не установлено это название, то поле не передаётся,
     *     клиенты на своей стороне подменят на "владелец" или "админ".
     */
    public function __construct(?int $userId = null, ?array $permissions = null, ?string $alias = null)
    {
        $this->required = ['user_id', 'permissions'];

        if ($userId !== null) {
            $this->setUserId($userId);
        }
        if ($permissions !== null) {
            $this->setPermissions($permissions);
        }
        if ($alias !== null) {
            $this->setAlias($alias);
        }
    }

    public function getAlias(): ?string
    {
        return $this->data['alias'] ?? null;
    }

    /**
     * @return list<ChatAdminPermission>
     */
    public function getPermissions(): array
    {
        return $this->data['permissions'];
    }

    public function getUserId(): int
    {
        return $this->data['user_id'];
    }

    public function issetAlias(): bool
    {
        return array_key_exists('alias', $this->data);
    }

    public function issetPermissions(): bool
    {
        return array_key_exists('permissions', $this->data);
    }

    public function issetUserId(): bool
    {
        return array_key_exists('user_id', $this->data);
    }

    /**
     * @param int $userId Идентификатор пользователя-участника чата, который назначается администратором.
     * @param ChatAdminPermission[] $permissions Перечень прав доступа пользователя.
     * @param non-empty-string|null $alias Заголовок, который будет показан на клиенте (minLength: 1).
     *     Если пользователь администратор или владелец и ему не установлено это название, то поле не передаётся,
     *     клиенты на своей стороне подменят на "владелец" или "админ".
     */
    public static function make(int $userId, array $permissions, ?string $alias = null): self
    {
        return new self($userId, $permissions, $alias);
    }

    /**
     * @param int|null $userId Идентификатор пользователя-участника чата, который назначается администратором.
     * @param ChatAdminPermission[]|null $permissions Перечень прав доступа пользователя.
     * @param non-empty-string|null $alias Заголовок, который будет показан на клиенте (minLength: 1).
     *     Если пользователь администратор или владелец и ему не установлено это название, то поле не передаётся,
     *     клиенты на своей стороне подменят на "владелец" или "админ".
     */
    public static function new(?int $userId = null, ?array $permissions = null, ?string $alias = null): self
    {
        return new self($userId, $permissions, $alias);
    }

    /**
     * @param non-empty-string $alias Заголовок, который будет показан на клиенте (minLength: 1).
     *     Если пользователь администратор или владелец и ему не установлено это название, то поле не передаётся,
     *     клиенты на своей стороне подменят на "владелец" или "админ".
     * @return $this
     */
    public function setAlias(string $alias): self
    {
        self::validateString('alias', $alias, minLength: 1);

        $this->data['alias'] = $alias;

        return $this;
    }

    /**
     * @param ChatAdminPermission[] $permissions Перечень прав доступа пользователя.
     * @return $this
     */
    public function setPermissions(array $permissions): self
    {
        $this->data['permissions'] = array_values(array_unique($permissions));

        return $this;
    }

    /**
     * @param int $userId Идентификатор пользователя-участника чата, который назначается администратором.
     * @return $this
     */
    public function setUserId(int $userId): self
    {
        $this->data['user_id'] = $userId;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetAlias(): self
    {
        unset($this->data['alias']);

        return $this;
    }
}
