<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

use MaxMessenger\Bot\Model\Response\FailedUserDetails;
use MaxMessenger\Bot\Model\Response\ModifyMembersResult;

/**
 * Ошибка изменения списка участников группового чата.
 *
 * @method ModifyMembersResult getResult()
 */
final class ModifyMembersError extends SimpleQueryError
{
    /**
     * @param ModifyMembersResult $result Исходный результат изменения списка участников.
     */
    public function __construct(
        ModifyMembersResult $result,
    ) {
        parent::__construct($result);
    }

    /**
     * @return list<FailedUserDetails>|null Подробное объяснение, почему пользователь не был добавлен
     *     в групповой чат.
     */
    public function getFailedUserDetails(): ?array
    {
        return $this->getResult()->getFailedUserDetails();
    }

    /**
     * @return list<int>|null Идентификаторы пользователей, которых не удалось добавить в групповой чат
     *     или удалить из него.
     */
    public function getFailedUserIds(): ?array
    {
        return $this->getResult()->getFailedUserIds();
    }
}
