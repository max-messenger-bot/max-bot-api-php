<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\CommentMessageBody;

use function array_key_exists;
use function is_string;

/**
 * Тело нового комментария к посту в канале.
 *
 * В отличие от сообщений в чатах и постов в каналах ({@see NewMessageBody}), в комментариях
 * не поддерживаются вложения `attachments` и пересылка сообщения (тип `forward`).
 *
 * Текст комментария обязателен и не может быть пустой строкой.
 *
 * @link https://dev.max.ru/docs-api/objects/NewCommentBody
 */
final class NewCommentBody extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     text: non-empty-string,
     *     link?: NewMessageLink,
     *     format?: TextFormat
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Текст комментария (maxLength: 4000).
     * @param NewMessageLink|null $link Ссылка на комментарий.
     * @param TextFormat|null $format Если установлен, текст комментария будет форматирован данным способом.
     */
    public function __construct(
        ?string $text = null,
        ?NewMessageLink $link = null,
        ?TextFormat $format = null,
    ) {
        $this->required = ['text'];

        if ($text !== null) {
            $this->setText($text);
        }
        if ($link !== null) {
            $this->setLink($link);
        }
        if ($format !== null) {
            $this->setFormat($format);
        }
    }

    /**
     * Добавляет текст к комментарию с новой строки.
     *
     * @param non-empty-string $appendedText Добавляемый текст (maxLength: 4000).
     * @return $this
     */
    public function addLine(string $appendedText): self
    {
        $text = $this->data['text'] ?? '';

        return $this->setText($text === '' ? $appendedText : "$text\n$appendedText");
    }

    /**
     * Добавляет текст к комментарию.
     *
     * @param non-empty-string $appendedText Добавляемый текст (maxLength: 4000).
     * @return $this
     */
    public function addText(string $appendedText): self
    {
        return $this->setText(($this->data['text'] ?? '') . $appendedText);
    }

    public function getFormat(): ?TextFormat
    {
        return $this->data['format'] ?? null;
    }

    public function getLink(): ?NewMessageLink
    {
        return $this->data['link'] ?? null;
    }

    /**
     * @return non-empty-string
     */
    public function getText(): string
    {
        return $this->data['text'];
    }

    public function issetFormat(): bool
    {
        return array_key_exists('format', $this->data);
    }

    public function issetLink(): bool
    {
        return array_key_exists('link', $this->data);
    }

    public function issetText(): bool
    {
        return array_key_exists('text', $this->data);
    }

    /**
     * @param non-empty-string|null $text Текст комментария (maxLength: 4000).
     * @param NewMessageLink|null $link Ссылка на комментарий.
     * @param TextFormat|null $format Если установлен, текст комментария будет форматирован данным способом.
     */
    public static function make(
        ?string $text = null,
        ?NewMessageLink $link = null,
        ?TextFormat $format = null,
    ): self {
        return new self($text, $link, $format);
    }

    /**
     * @param non-empty-string|null $text Текст комментария (maxLength: 4000).
     * @param NewMessageLink|null $link Ссылка на комментарий.
     * @param TextFormat|null $format Если установлен, текст комментария будет форматирован данным способом.
     */
    public static function new(
        ?string $text = null,
        ?NewMessageLink $link = null,
        ?TextFormat $format = null,
    ): self {
        return new self($text, $link, $format);
    }

    /**
     * @param TextFormat $format Если установлен, текст комментария будет форматирован данным способом.
     * @return $this
     */
    public function setFormat(TextFormat $format): self
    {
        $this->data['format'] = $format;

        return $this;
    }

    /**
     * @param NewMessageLink $link Ссылка на комментарий.
     * @return $this
     */
    public function setLink(NewMessageLink $link): self
    {
        $this->data['link'] = $link;

        return $this;
    }

    /**
     * Устанавливает ссылку на комментарий, на который отправляется ответ.
     *
     * @param non-empty-string|CommentMessage|CommentMessageBody $mid Уникальный ID комментария или объект комментария.
     * @return $this
     */
    public function setReplyLink(string|CommentMessage|CommentMessageBody $mid): self
    {
        $this->data['link'] = is_string($mid)
            ? new NewMessageLink($mid)
            : NewMessageLink::newFromMessage($mid);

        return $this;
    }

    /**
     * @param non-empty-string $text Текст комментария (maxLength: 4000).
     * @return $this
     */
    public function setText(string $text): self
    {
        self::validateString('text', $text, minLength: 1, maxLength: 4000);

        $this->data['text'] = $text;

        return $this;
    }

    public function unsetFormat(): self
    {
        unset($this->data['format']);

        return $this;
    }

    public function unsetLink(): self
    {
        unset($this->data['link']);

        return $this;
    }
}
