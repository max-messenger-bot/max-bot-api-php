<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Подробное объяснение, почему пользователь не был добавлен в групповой чат.
 *
 * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/
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
     * @return string Код ошибки:
     *     - `add.participant.privacy` — Добавление пользователей запрещено настройками конфиденциальности.
     *     - `add.participant.not.found` — Пользователи не найдены.
     */
    public function getErrorCode(): string
    {
        return $this->data['error_code'];
    }

    /**
     * @return list<int> Идентификаторы пользователей с данной ошибкой.
     */
    public function getUserIds(): array
    {
        return $this->data['user_ids'];
    }
}
