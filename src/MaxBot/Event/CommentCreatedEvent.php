<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\CommentCreatedUpdate;
use MaxMessenger\Bot\Model\Response\CommentMessage;

/**
 * Событие создания комментария к посту в канале.
 *
 * Бот получает событие, только если он администратор канала с правом {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read CommentCreatedUpdate $update
 */
final class CommentCreatedEvent extends BaseEvent
{
    use CommentEventTrait;

    /**
     * @return CommentMessage Новый созданный комментарий.
     */
    public function getComment(): CommentMessage
    {
        return $this->update->getMessage();
    }
}
