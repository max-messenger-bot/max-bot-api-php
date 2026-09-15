<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\CommentCreatedUpdate;
use MaxMessenger\Bot\Model\Response\CommentMessage;

/**
 * Событие создания комментария к посту в канале.
 *
 * На свои действия бот события не получает.
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
