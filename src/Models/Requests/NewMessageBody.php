<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\TextFormat;

use function array_key_exists;

/**
 * New message body.
 *
 * @api
 */
final class NewMessageBody extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     attachments: list<AttachmentRequest>|null,
     *     format?: TextFormat|null,
     *     link: NewMessageLink|null,
     *     notify: bool,
     *     text: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param string|null $text Message text (maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Message attachments.
     * @param NewMessageLink|null $link Link to message.
     * @param bool $notify If false, chat participants would not be notified.
     * @param TextFormat|null $format If set, message text will be formatted according to given markup.
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
     * @param string|null $text Message text (maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Message attachments.
     * @param NewMessageLink|null $link Link to message.
     * @param bool $notify If false, chat participants would not be notified.
     * @param TextFormat|null $format If set, message text will be formatted according to given markup.
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
     * @param AttachmentRequest[]|null $attachments Message attachments.
     * @api
     */
    public function setAttachments(?array $attachments = null): static
    {
        $this->data['attachments'] = $attachments !== null ? array_values($attachments) : null;

        return $this;
    }

    /**
     * @param TextFormat|null $format If set, message text will be formatted according to given markup.
     * @api
     */
    public function setFormat(?TextFormat $format = null): static
    {
        $this->data['format'] = $format;

        return $this;
    }

    /**
     * @param NewMessageLink|null $link Link to message.
     * @api
     */
    public function setLink(?NewMessageLink $link = null): static
    {
        $this->data['link'] = $link;

        return $this;
    }

    /**
     * @param bool $notify If false, chat participants would not be notified.
     * @api
     */
    public function setNotify(bool $notify): static
    {
        $this->data['notify'] = $notify;

        return $this;
    }

    /**
     * @param string|null $text Message text (maxLength: 4000).
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
