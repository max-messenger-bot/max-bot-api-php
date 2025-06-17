<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\MessageLinkType;
use MaxMessenger\Bot\Models\Enums\TextFormat;
use MaxMessenger\Bot\Models\Responses\Message;
use MaxMessenger\Bot\Models\Responses\MessageBody;

use function array_key_exists;
use function is_string;

/**
 * Новое тело сообщения.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
final class NewMessageBody extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     text?: non-empty-string,
     *     attachments?: list<AttachmentRequest>,
     *     link?: NewMessageLink,
     *     notify: bool,
     *     format?: TextFormat
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Текст сообщения (minLength: 1, maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение.
     * @param bool $notify Если false, участники чата не будут уведомлены (по умолчанию `true`).
     * @param TextFormat|null $format Если установлен, текст сообщения будет форматирован данным способом.
     */
    public function __construct(
        ?string $text = null,
        ?array $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null
    ) {
        $this->requiredOnce = ['text', 'attachments', 'link'];

        if ($text !== null) {
            $this->setText($text);
        }
        if ($attachments !== null) {
            $this->setAttachments($attachments);
        }
        if ($link !== null) {
            $this->setLink($link);
        }
        $this->setNotify($notify);
        if ($format !== null) {
            $this->setFormat($format);
        }
    }

    /**
     * Прикрепить вложение к сообщению.
     *
     * @param AttachmentRequest $attachment Вложение для сообщения.
     * @return $this
     */
    public function addAttachment(AttachmentRequest $attachment): self
    {
        $this->data['attachments'][] = $attachment;

        return $this;
    }

    /**
     * Прикрепить аудио к сообщению.
     *
     *  Должно быть единственным вложением в сообщении.
     *
     * @param non-empty-string $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     * @return $this
     */
    public function addAudioAttachment(string $token): self
    {
        $this->data['attachments'][] = new AudioAttachmentRequest(new UploadedInfo($token));

        return $this;
    }

    /**
     * Прикрепить карточку контакта к сообщению.
     *
     * @param non-empty-string|null $vcfInfo
     * @return $this
     */
    public function addContactAttachment(?int $contactId = null, ?string $vcfInfo = null): self
    {
        $this->data['attachments'][] = new ContactAttachmentRequest(
            new ContactAttachmentRequestPayload($contactId, $vcfInfo)
        );

        return $this;
    }

    /**
     * Прикрепить файл к сообщению.
     *
     * Должен быть единственным вложением в сообщении.
     *
     * @param non-empty-string $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     * @return $this
     */
    public function addFileAttachment(string $token): self
    {
        $this->data['attachments'][] = new FileAttachmentRequest(new UploadedInfo($token));

        return $this;
    }

    /**
     * Прикрепить изображение к сообщению.
     *
     * @param non-empty-string $token Токен существующего вложения (minLength: 1).
     * @return $this
     */
    public function addImageAttachment(string $token): self
    {
        $this->data['attachments'][] = new PhotoAttachmentRequest(new PhotoAttachmentRequestPayload(token: $token));

        return $this;
    }

    /**
     * Прикрепить встроенную клавиатуру к сообщению и вернуть модель с для наполнения клавиатуры кнопками.
     *
     * Вы можете подключить к чат-боту в MAX inline-клавиатуру. Она позволяет разместить под сообщением бота
     * до 210 кнопок, сгруппированных в 30 рядов — до 7 кнопок в каждом (до 3, если это кнопки типа `link`, `open_app`,
     * `request_geo_location` или `request_contact`).
     */
    public function addInlineKeyboard(): InlineKeyboardAttachmentRequestPayload
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();
        $this->data['attachments'][] = new InlineKeyboardAttachmentRequest($payload);

        return $payload;
    }

    /**
     * Прикрепить встроенную клавиатуру к сообщению.
     *
     * Вы можете подключить к чат-боту в MAX inline-клавиатуру. Она позволяет разместить под сообщением бота
     * до 210 кнопок, сгруппированных в 30 рядов — до 7 кнопок в каждом (до 3, если это кнопки типа `link`, `open_app`,
     * `request_geo_location` или `request_contact`).
     *
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок (minItems: 1).
     * @return $this
     */
    public function addInlineKeyboardAttachment(array $buttons): self
    {
        $this->data['attachments'][] = new InlineKeyboardAttachmentRequest(
            new InlineKeyboardAttachmentRequestPayload($buttons)
        );

        return $this;
    }

    /**
     * Прикрепить координаты локации к сообщению.
     *
     * @param float $latitude Широта.
     * @param float $longitude Долгота.
     * @return $this
     */
    public function addLocationAttachment(float $latitude, float $longitude): self
    {
        $this->data['attachments'][] = new LocationAttachmentRequest($latitude, $longitude);

        return $this;
    }

    /**
     * Прикрепить предпросмотр медиафайла по-внешнему URL.
     *
     * @param non-empty-string $url URL, прикрепленный к сообщению в качестве предпросмотра медиа (minLength: 1).
     * @param non-empty-string|null $token Токен вложения (minLength: 1).
     * @return $this
     */
    public function addShareAttachment(string $url, ?string $token = null): self
    {
        $this->data['attachments'][] = new ShareAttachmentRequest(new ShareAttachmentPayload($url, $token));

        return $this;
    }

    /**
     * Прикрепить стикер к сообщению.
     *
     * Должен быть единственным вложением в сообщении.
     *
     * @param non-empty-string $code Код стикера (minLength: 1).
     * @return $this
     */
    public function addStickerAttachment(string $code): self
    {
        $this->data['attachments'][] = new StickerAttachmentRequest(new StickerAttachmentRequestPayload($code));

        return $this;
    }

    /**
     * Прикрепить изображение к сообщению.
     *
     * @param non-empty-string $url Любой внешний URL изображения, которое вы хотите прикрепить (minLength: 1)
     * @return $this
     */
    public function addUrlImageAttachment(string $url): self
    {
        $this->data['attachments'][] = new PhotoAttachmentRequest(new PhotoAttachmentRequestPayload(url: $url));

        return $this;
    }

    /**
     * Прикрепить видео к сообщению.
     *
     * @param non-empty-string $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     * @return $this
     */
    public function addVideoAttachment(string $token): self
    {
        $this->data['attachments'][] = new VideoAttachmentRequest(new UploadedInfo($token));

        return $this;
    }

    /**
     * @return list<AttachmentRequest>|null
     */
    public function getAttachments(): ?array
    {
        return $this->data['attachments'] ?? null;
    }

    /**
     * @return TextFormat|null
     */
    public function getFormat(): ?TextFormat
    {
        return $this->data['format'] ?? null;
    }

    /**
     * @return NewMessageLink|null
     */
    public function getLink(): ?NewMessageLink
    {
        return $this->data['link'] ?? null;
    }

    /**
     * @return bool
     */
    public function getNotify(): bool
    {
        return $this->data['notify'] ?? true;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->data['text'] ?? null;
    }

    public function issetAttachments(): bool
    {
        return array_key_exists('attachments', $this->data);
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
     * @param non-empty-string|null $text Текст сообщения (minLength: 1, maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение.
     * @param bool $notify Если false, участники чата не будут уведомлены (по умолчанию `true`).
     * @param TextFormat|null $format Если установлен, текст сообщения будет форматирован данным способом.
     */
    public static function make(
        ?string $text = null,
        array|null $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null
    ): self {
        return new self($text, $attachments, $link, $notify, $format);
    }

    /**
     * @param non-empty-string|null $text Текст сообщения (minLength: 1, maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение.
     * @param bool $notify Если false, участники чата не будут уведомлены (по умолчанию `true`).
     * @param TextFormat|null $format Если установлен, текст сообщения будет форматирован данным способом.
     */
    public static function new(
        ?string $text = null,
        array|null $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null
    ): self {
        return new self($text, $attachments, $link, $notify, $format);
    }

    /**
     * @param AttachmentRequest[] $attachments Вложения сообщения. Если пусто, все вложения будут удалены.
     * @return $this
     */
    public function setAttachments(array $attachments): self
    {
        $this->data['attachments'] = array_values($attachments);

        return $this;
    }

    /**
     * @param TextFormat $format Если установлен, текст сообщения будет форматирован данным способом.
     * @return $this
     */
    public function setFormat(TextFormat $format): self
    {
        $this->data['format'] = $format;

        return $this;
    }

    /**
     * @param non-empty-string|Message|MessageBody $mid Уникальный ID сообщения или объект сообщения.
     * @return $this
     */
    public function setForwardLink(string|Message|MessageBody $mid): self
    {
        $this->data['link'] = is_string($mid)
            ? new NewMessageLink($mid, MessageLinkType::Forward)
            : NewMessageLink::newFromMessage($mid, MessageLinkType::Forward);

        return $this;
    }

    /**
     * @param NewMessageLink $link Ссылка на сообщение.
     * @return $this
     */
    public function setLink(NewMessageLink $link): self
    {
        $this->data['link'] = $link;

        return $this;
    }

    /**
     * @param bool $notify Если false, участники чата не будут уведомлены (по умолчанию `true`).
     * @return $this
     */
    public function setNotify(bool $notify): self
    {
        $this->data['notify'] = $notify;

        return $this;
    }

    /**
     * @param non-empty-string|Message|MessageBody $mid Уникальный ID сообщения или объект сообщения.
     * @return $this
     */
    public function setReplyLink(string|Message|MessageBody $mid): self
    {
        $this->data['link'] = is_string($mid)
            ? new NewMessageLink($mid)
            : NewMessageLink::newFromMessage($mid);

        return $this;
    }

    /**
     * @param non-empty-string $text Текст сообщения (minLength: 1, maxLength: 4000).
     * @return $this
     */
    public function setText(string $text): self
    {
        self::validateString('text', $text, minLength: 1, maxLength: 4000);

        $this->data['text'] = $text;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetAttachments(): self
    {
        unset($this->data['attachments']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetFormat(): self
    {
        unset($this->data['format']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetLink(): self
    {
        unset($this->data['link']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetText(): self
    {
        unset($this->data['text']);

        return $this;
    }
}
