<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use function array_key_exists;
use function array_map;
use function array_unique;
use function array_values;
use function in_array;

/**
 * Список идентификаторов пользователей для добавления в групповой чат.
 *
 * Не более 100 элементов.
 *
 * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/
 */
final class UserIdsList extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     user_ids: non-empty-list<int>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-array<int>|null $userIds Массив идентификаторов пользователей, которых вы хотите добавить
     *     в групповой чат (maxItems: 100).
     */
    public function __construct(?array $userIds = null)
    {
        $this->required = ['user_ids'];

        if ($userIds !== null) {
            $this->setUserIds($userIds);
        }
    }

    /**
     * Добавляет идентификатор пользователя в список, если его там ещё нет.
     *
     * @param int $userId Идентификатор пользователя.
     * @return $this
     */
    public function addUserId(int $userId): self
    {
        if (!array_key_exists('user_ids', $this->data)) {
            $this->data['user_ids'] = [$userId];

            return $this;
        }

        if (!in_array($userId, $this->data['user_ids'], true)) {
            $userIds = [...$this->data['user_ids'], $userId];

            self::validateArray('userId', $userIds, maxItems: 100);

            $this->data['user_ids'] = $userIds;
        }

        return $this;
    }

    /**
     * @return non-empty-list<int>
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
     * @param non-empty-array<int> $userIds Массив идентификаторов пользователей, которых вы хотите добавить
     *     в групповой чат (maxItems: 100).
     */
    public static function make(array $userIds): self
    {
        return new self($userIds);
    }

    /**
     * @param non-empty-array<int>|null $userIds Массив идентификаторов пользователей, которых вы хотите добавить
     *     в групповой чат (maxItems: 100).
     */
    public static function new(?array $userIds = null): self
    {
        return new self($userIds);
    }

    /**
     * @param non-empty-array<int> $userIds Массив идентификаторов пользователей, которых вы хотите добавить
     *     в групповой чат (maxItems: 100).
     * @return $this
     */
    public function setUserIds(array $userIds): self
    {
        $userIds = array_values(array_unique(array_map('\intval', $userIds)));

        self::validateArray('userIds', $userIds, minItems: 1, maxItems: 100);

        $this->data['user_ids'] = $userIds;

        return $this;
    }
}
