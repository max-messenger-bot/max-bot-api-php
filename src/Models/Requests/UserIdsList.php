<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function in_array;

/**
 * @template-extends BaseRequestModel<array{user_ids: list<int>}>
 * @api
 */
class UserIdsList extends BaseRequestModel
{
    /**
     * @param int[] $userIds
     * @api
     */
    public function __construct(array $userIds = [])
    {
        $this->setUserIds($userIds);
    }

    /**
     * @return $this
     * @api
     */
    public function addUserId(int $userId): static
    {
        if (!in_array($userId, $this->data['user_ids'], true)) {
            $this->data['user_ids'][] = $userId;
        }

        return $this;
    }

    /**
     * @return list<int>
     * @api
     */
    public function getUserIds(): array
    {
        return $this->data['user_ids'];
    }

    /**
     * @return $this
     * @api
     */
    public function removeUserId(int $userId): static
    {
        $this->data['user_ids'] = array_values(array_diff($this->data['user_ids'], [$userId]));

        return $this;
    }

    /**
     * @param int[] $userIds
     * @return $this
     * @api
     */
    public function setUserIds(array $userIds): static
    {
        $this->data['user_ids'] = static::prepareUserIds($userIds);

        return $this;
    }

    /**
     * @param int[] $userIds
     * @return list<int>
     * @api
     */
    protected static function prepareUserIds(array $userIds): array
    {
        return array_values(array_unique(array_map('\intval', $userIds)));
    }
}
