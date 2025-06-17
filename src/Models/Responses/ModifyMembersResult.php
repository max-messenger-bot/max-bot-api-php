<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Результат запроса на изменение списка участников.
 */
class ModifyMembersResult extends SimpleQueryResult
{
    /**
     * @var array{
     *     failed_user_ids?: list<int>,
     *     failed_user_details?: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<FailedUserDetails>|false|null
     */
    private array|false|null $failedUserDetails = false;

    /**
     * @return list<FailedUserDetails>|null Подробное описание, почему пользователь не был добавлен в чат.
     */
    public function getFailedUserDetails(): ?array
    {
        return $this->failedUserDetails === false
            ? ($this->failedUserDetails = FailedUserDetails::newListFromNullableData(
                $this->data['failed_user_details'] ?? null
            ))
            : $this->failedUserDetails;
    }

    /**
     * @return list<int>|null ID пользователей, которых не удалось добавить.
     */
    public function getFailedUserIds(): ?array
    {
        return $this->data['failed_user_ids'] ?? null;
    }
}
