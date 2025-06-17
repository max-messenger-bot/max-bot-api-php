<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\TextFormat;

use function array_key_exists;

/**
 * Новое тело сообщения.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 * @api
 */
final class NewMessageBody extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     text: string|null,
     *     attachments: list<AttachmentRequest>|null,
     *     link: NewMessageLink|null,
     *     notify: bool,
     *     format?: TextFormat|null
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param string|null $text Новый текст сообщения (maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение.
     * @param bool $notify Если false, участники чата не будут уведомлены (по умолчанию `true`).
     * @param TextFormat|null $format Если установлен, текст сообщения будет форматирован данным способом.
     * @api
     */
    public function __construct(
        ?string $text = null,
        ?array $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null
    ) {
        $this->setText($text);
        $this->setAttachments($attachments);
        $this->setLink($link);
        $this->setNotify($notify);
        if ($format !== null) {
            $this->setFormat($format);
        }
    }

    /**
     * @return list<AttachmentRequest>|null
     * @api
     */
    public function getAttachments(): ?array
    {
        return $this->data['attachments'];
    }

    /**
     * @api
     */
    public function getFormat(): ?TextFormat
    {
        return $this->data['format'] ?? null;
    }

    /**
     * @api
     */
    public function getLink(): ?NewMessageLink
    {
        return $this->data['link'];
    }

    /**
     * @api
     */
    public function getNotify(): bool
    {
        return $this->data['notify'];
    }

    /**
     * @api
     */
    public function getText(): ?string
    {
        return $this->data['text'];
    }

    /**
     * @api
     */
    public function issetFormat(): bool
    {
        return array_key_exists('format', $this->data);
    }

    /**
     * @param string|null $text Новый текст сообщения (maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение.
     * @param bool $notify Если false, участники чата не будут уведомлены (по умолчанию `true`).
     * @param TextFormat|null $format Если установлен, текст сообщения будет форматирован данным способом.
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(
        ?string $text = null,
        ?array $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null
    ): static {
        return new static($text, $attachments, $link, $notify, $format);
    }

    /**
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @return $this
     * @api
     */
    public function setAttachments(?array $attachments = null): static
    {
        $this->data['attachments'] = $attachments !== null ? array_values($attachments) : null;

        return $this;
    }

    /**
     * @param TextFormat|null $format Если установлен, текст сообщения будет форматирован данным способом.
     * @return $this
     * @api
     */
    public function setFormat(?TextFormat $format = null): static
    {
        $this->data['format'] = $format;

        return $this;
    }

    /**
     * @param NewMessageLink|null $link Ссылка на сообщение.
     * @return $this
     * @api
     */
    public function setLink(?NewMessageLink $link = null): static
    {
        $this->data['link'] = $link;

        return $this;
    }

    /**
     * @param bool $notify Если false, участники чата не будут уведомлены (по умолчанию `true`).
     * @return $this
     * @api
     */
    public function setNotify(bool $notify): static
    {
        $this->data['notify'] = $notify;

        return $this;
    }

    /**
     * @param string|null $text Новый текст сообщения (maxLength: 4000).
     * @return $this
     * @api
     */
    public function setText(?string $text = null): static
    {
        static::validateString('text', $text, maxLength: 4000);

        $this->data['text'] = $text;

        return $this;
    }

    /**
     * @api
     */
    public function unsetFormat(): static
    {
        unset($this->data['format']);

        return $this;
    }
}
