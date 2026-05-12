<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\Validation\MaxItemsException;

use function array_key_exists;
use function count;
use function in_array;

/**
 * Список идентификаторов пользователей.
 *
 * Не более 100 элементов.
 */
final class UserIdsList extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     user_ids: list<int>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-array<int>|null $userIds Массив ID пользователей для добавления в чат
     * (minItems: 1, maxItems: 100).
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
            if (count($this->data['user_ids']) >= 100) {
                throw new MaxItemsException('user_ids', 101, 100);
            }

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
     * @param non-empty-array<int> $userIds Массив ID пользователей для добавления в чат (minItems: 1, maxItems: 100).
     */
    public static function make(array $userIds): self
    {
        return new self($userIds);
    }

    /**
     * @param non-empty-array<int>|null $userIds Массив ID пользователей для добавления в чат (maxItems: 100).
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
     * @param non-empty-array<int> $userIds Массив ID пользователей для добавления в чат (minItems: 1, maxItems: 100).
     * @return $this
     */
    public function setUserIds(array $userIds): self
    {
        $this->data['user_ids'] = static::prepareUserIds($userIds);

        return $this;
    }

    /**
     * @param non-empty-array<int> $userIds Массив ID пользователей для добавления в чат (minItems: 1, maxItems: 100).
     * @return non-empty-list<int>
     */
    protected static function prepareUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_map('\intval', $userIds)));

        self::validateArray('user_ids', $userIds, minItems: 1, maxItems: 100);

        return $userIds;
    }
}
