<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\CommentEditedUpdate;
use MaxMessenger\Bot\Model\Response\CommentMessage;

/**
 * Событие редактирования комментария к посту в канале.
 *
 * На свои действия бот события не получает.
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
