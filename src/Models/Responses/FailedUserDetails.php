<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Подробное описание, почему пользователь не был добавлен в чат.
 */
class FailedUserDetails extends BaseResponseModel
{
    /**
     * @var array{
     *     error_code: string,
     *     user_ids: list<int>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string Код ошибки.
     */
    public function getErrorCode(): string
    {
        return $this->data['error_code'];
    }

    /**
     * @return list<int> ID пользователей с данной ошибкой.
     */
    public function getUserIds(): array
    {
        return $this->data['user_ids'];
    }
}
