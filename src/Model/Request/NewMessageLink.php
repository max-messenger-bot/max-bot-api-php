<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\CommentMessageBody;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageBody;

use function array_key_exists;

/**
 * Ссылка на сообщение в чате, пост в канале или комментарий к посту.
 *
 * Пересылка комментариев не поддерживается.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody/
 * @link https://dev.max.ru/docs-api/objects/NewCommentBody/
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/methods/PUT/messages/
 * @link https://dev.max.ru/docs-api/methods/POST/answers/
 * @link https://dev.max.ru/docs-api/methods/POST/messages/-messageId-/comments/
 * @link https://dev.max.ru/docs-api/methods/PUT/messages/-messageId-/comments/
 */
final class NewMessageLink extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     type: MessageLinkType,
     *     mid: non-empty-string,
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $mid Идентификатор исходного сообщения, поста или комментария
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     * @param MessageLinkType $type Тип ссылки на сообщение, пост или комментарий.
     */
    public function __construct(?string $mid = null, MessageLinkType $type = MessageLinkType::Reply)
    {
        $this->required = ['mid'];

        if ($mid !== null) {
            $this->setMid($mid);
        }
        $this->setType($type);
    }

    /**
     * @return non-empty-string
     */
    public function getMid(): string
    {
        return $this->data['mid'];
    }

    public function getType(): MessageLinkType
    {
        return $this->data['type'];
    }

    public function issetMid(): bool
    {
        return array_key_exists('mid', $this->data);
    }

    /**
     * @param non-empty-string $mid Идентификатор исходного сообщения, поста или комментария
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     * @param MessageLinkType $type Тип ссылки на сообщение, пост или комментарий.
     */
    public static function make(string $mid, MessageLinkType $type): self
    {
        return new self($mid, $type);
    }

    /**
     * @param non-empty-string|null $mid Идентификатор исходного сообщения, поста или комментария
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     * @param MessageLinkType $type Тип ссылки на сообщение, пост или комментарий.
     */
    public static function new(?string $mid = null, MessageLinkType $type = MessageLinkType::Reply): self
    {
        return new self($mid, $type);
    }

    /**
     * @param Message|MessageBody|CommentMessage|CommentMessageBody $message Исходное сообщение, пост или комментарий,
     *     из которого берётся идентификатор.
     * @param MessageLinkType $type Тип связи: ответ или пересылка.
     */
    public static function newFromMessage(
        Message|MessageBody|CommentMessage|CommentMessageBody $message,
        MessageLinkType $type = MessageLinkType::Reply,
    ): self {
        if ($message instanceof Message || $message instanceof CommentMessage) {
            $message = $message->getBody();
        }

        return new self($message->getMid(), $type);
    }

    /**
     * @param non-empty-string $mid Идентификатор исходного сообщения, поста или комментария
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     * @return $this
     */
    public function setMid(string $mid): self
    {
        self::validateMid('mid', $mid);

        $this->data['mid'] = $mid;

        return $this;
    }

    /**
     * @param MessageLinkType $type Тип ссылки на сообщение, пост или комментарий.
     * @return $this
     */
    public function setType(MessageLinkType $type): self
    {
        $this->data['type'] = $type;

        return $this;
    }
}
