<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

use function array_key_exists;
use function array_unique;
use function array_values;

/**
 * Администратор чата с правами доступа.
 *
 * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/admins/
 */
final class ChatAdmin extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

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
     * @param int|null $userId Идентификатор пользователя или бота, участвующего в групповом чате или канале,
     *     который назначается администратором.
     * @param ChatAdminPermission[]|null $permissions Перечень прав доступа пользователя или бота, который
     *     назначается администратором группового чата или канала.
     * @param non-empty-string|null $alias Псевдоним администратора в чате (maxLength: 16).
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

    /**
     * @return non-empty-string|null
     */
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
     * @param int $userId Идентификатор пользователя или бота, участвующего в групповом чате или канале,
     *     который назначается администратором.
     * @param ChatAdminPermission[] $permissions Перечень прав доступа пользователя или бота, который
     *     назначается администратором группового чата или канала.
     * @param non-empty-string|null $alias Псевдоним администратора в чате (maxLength: 16).
     */
    public static function make(int $userId, array $permissions, ?string $alias = null): self
    {
        return new self($userId, $permissions, $alias);
    }

    /**
     * @param int|null $userId Идентификатор пользователя или бота, участвующего в групповом чате или канале,
     *     который назначается администратором.
     * @param ChatAdminPermission[]|null $permissions Перечень прав доступа пользователя или бота, который
     *     назначается администратором группового чата или канала.
     * @param non-empty-string|null $alias Псевдоним администратора в чате (maxLength: 16).
     */
    public static function new(?int $userId = null, ?array $permissions = null, ?string $alias = null): self
    {
        return new self($userId, $permissions, $alias);
    }

    /**
     * @param non-empty-string $alias Псевдоним администратора в чате (maxLength: 16).
     * @return $this
     */
    public function setAlias(string $alias): self
    {
        self::validateString('alias', $alias, minLength: 1, maxLength: 16);

        $this->data['alias'] = $alias;

        return $this;
    }

    /**
     * @param ChatAdminPermission[] $permissions Перечень прав доступа пользователя или бота, который
     *     назначается администратором группового чата или канала.
     * @return $this
     */
    public function setPermissions(array $permissions): self
    {
        $this->data['permissions'] = array_values(array_unique($permissions, SORT_REGULAR));

        return $this;
    }

    /**
     * @param int $userId Идентификатор пользователя или бота, участвующего в групповом чате или канале,
     *     который назначается администратором.
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
