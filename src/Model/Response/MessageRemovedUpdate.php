<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Событие удаления сообщения.
 *
 * В групповом чате бот получает событие, только если он администратор с правом
 * {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class MessageRemovedUpdate extends Update
{
    /**
     * @var array{
     *     message_id: non-empty-string,
     *     chat_id: int,
     *     user_id: int
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Идентификатор чата, где сообщение было удалено.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return non-empty-string Идентификатор удалённого сообщения.
     */
    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['message_id', 'chat_id', 'user_id'];
    }

    /**
     * @return int Идентификатор пользователя, удалившего сообщение.
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }
}
