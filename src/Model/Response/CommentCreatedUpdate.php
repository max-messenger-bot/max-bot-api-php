<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Событие создания комментария.
 *
 * Бот получает событие, только если он администратор канала с правом {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class CommentCreatedUpdate extends Update
{
    /**
     * @var array{
     *     message: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private CommentMessage|false $message = false;

    /**
     * @return CommentMessage Новый созданный комментарий.
     */
    public function getMessage(): CommentMessage
    {
        return $this->message === false
            ? $this->message = CommentMessage::newFromData($this->data['message'])
            : $this->message;
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['message'];
    }
}
