<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Список комментариев к посту в канале.
 */
class CommentMessageList extends BaseResponseModel
{
    /**
     * @var array{
     *     messages: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<CommentMessage>|false
     */
    private array|false $messages = false;

    /**
     * @return list<CommentMessage> Список комментариев к посту в канале.
     */
    public function getMessages(): array
    {
        return $this->messages === false
            ? ($this->messages = CommentMessage::newListFromData($this->data['messages']))
            : $this->messages;
    }
}
