<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\CommentEditedUpdate;
use MaxMessenger\Bot\Model\Response\CommentMessage;

/**
 * Событие редактирования комментария к посту в канале.
 *
 * Бот получает событие, только если он администратор канала с правом {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read CommentEditedUpdate $update
 */
final class CommentEditedEvent extends BaseEvent
{
    use CommentEventTrait;

    /**
     * @return CommentMessage Отредактированный комментарий.
     */
    public function getComment(): CommentMessage
    {
        return $this->update->getMessage();
    }
}
