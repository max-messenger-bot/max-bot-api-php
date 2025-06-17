<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;
use function in_array;

/**
 * Список идентификаторов пользователей.
 */
final class UserIdsList extends BaseRequestModel
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     user_ids: list<int>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param int[]|null $userIds Массив ID пользователей для добавления в чат.
     */
    public function __construct(?array $userIds = null)
    {
        $this->required = ['user_ids'];

        if ($userIds !== null) {
            $this->setUserIds($userIds);
        }
    }

    /**
     * @param int $userId Идентификатор пользователя.
     * @return $this
     */
    public function addUserId(int $userId): self
    {
        if (!in_array($userId, $this->data['user_ids'], true)) {
            $this->data['user_ids'][] = $userId;
        }

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getUserIds(): array
    {
        return $this->data['user_ids'];
    }

    public function issetUserIds(): bool
    {
        return array_key_exists('user_ids', $this->data);
    }

    /**
     * @param int[] $userIds Массив ID пользователей для добавления в чат.
     */
    public static function make(array $userIds): self
    {
        return new self($userIds);
    }

    /**
     * @param int[]|null $userIds Массив ID пользователей для добавления в чат.
     */
    public static function new(?array $userIds = null): self
    {
        return new self($userIds);
    }

    /**
     * @param int $userId Идентификатор пользователя.
     * @return $this
     */
    public function removeUserId(int $userId): self
    {
        $this->data['user_ids'] = array_values(array_diff($this->data['user_ids'], [$userId]));

        return $this;
    }

    /**
     * @param int[] $userIds Массив ID пользователей для добавления в чат.
     * @return $this
     */
    public function setUserIds(array $userIds): self
    {
        $this->data['user_ids'] = static::prepareUserIds($userIds);

        return $this;
    }

    /**
     * @param int[] $userIds Массив ID пользователей для добавления в чат.
     * @return list<int>
     */
    protected static function prepareUserIds(array $userIds): array
    {
        return array_values(array_unique(array_map('\intval', $userIds)));
    }
}
