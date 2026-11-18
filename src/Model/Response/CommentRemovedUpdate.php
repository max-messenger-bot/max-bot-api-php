<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Событие удаления комментария.
 *
 * Бот получает событие, только если он администратор канала с правом {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class CommentRemovedUpdate extends Update
{
    /**
     * @var array{
     *     message_id: non-empty-string,
     *     chat_id: int,
     *     user_id: int,
     *     post_id?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int Идентификатор чата, где комментарий был удалён.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return non-empty-string Идентификатор удалённого комментария.
     */
    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    /**
     * @return non-empty-string|null Идентификатор поста в канале, если он передан.
     */
    public function getPostId(): ?string
    {
        return $this->data['post_id'] ?? null;
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['message_id', 'chat_id', 'user_id'];
    }

    /**
     * @return int Идентификатор пользователя, удалившего комментарий.
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }
}
