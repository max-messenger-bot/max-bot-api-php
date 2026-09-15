<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вы получите это событие, как только комментарий будет создан.
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
}
