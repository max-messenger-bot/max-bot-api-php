<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageBody;

use function array_key_exists;
use function array_values;
use function is_string;

/**
 * Новое тело сообщения.
 *
 * Объект используется при отправке нового сообщения в чат или канал, редактировании существующего сообщения или поста,
 * а также при callback-отправке сообщения пользователю, нажавшему кнопку в чате или канале.
 *
 * Параметры объекта содержат текст и способ его форматирования, вложения, ссылку на связанное
 * сообщение (ответ или пересылка) и настройку PUSH-уведомлений для участников чата.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
final class NewMessageBody extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     text?: string,
     *     attachments?: list<AttachmentRequest>,
     *     link?: NewMessageLink,
     *     notify: bool,
     *     format?: TextFormat
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param string|null $text Текст сообщения (maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если поле не передано или равно `null`,
     *     изменений не произойдёт. Если массив пуст, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение в чате или пост в канале.
     *     Ссылки на комментарии к постам в каналах не поддерживаются.
     * @param bool $notify Если `false`, участники чата не получат push-уведомления (по умолчанию `true`).
     *     Для каналов нужно передавать `true` или не указывать параметр: каналы не подразумевают
     *     отправку постов без push-уведомлений.
     * @param TextFormat|null $format Разметка текста сообщения. Подробнее —
     *     {@link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting в разделе «Форматирование»}.
     */
    public function __construct(
        ?string $text = null,
        ?array $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null,
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
     * Прикрепляет вложение к сообщению.
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
     * Прикрепляет аудио к сообщению.
     *
     * @param non-empty-string $token Токен загруженного аудио.
     * @return $this
     */
    public function addAudioAttachment(string $token): self
    {
        $this->data['attachments'][] = new AudioAttachmentRequest(new UploadedInfo($token));

        return $this;
    }

    /**
     * Прикрепляет карточку контакта к сообщению.
     *
     * Нельзя прикреплять несколько контактов или сочетать контакт с другими вложениями, кроме клавиатуры.
     *
     * @param int|null $contactId Идентификатор пользователя, если он зарегистрирован в МАКС.
     * @param non-empty-string|null $vcfInfo Полная информация о контакте в формате VCF (minLength: 20).
     * @return $this
     */
    public function addContactAttachment(?int $contactId = null, ?string $vcfInfo = null): self
    {
        $this->data['attachments'][] = new ContactAttachmentRequest(
            new ContactAttachmentRequestPayload($contactId, $vcfInfo),
        );

        return $this;
    }

    /**
     * Прикрепляет файл к сообщению.
     *
     * Нельзя прикреплять несколько файлов или сочетать файл с другими вложениями, кроме клавиатуры.
     *
     * @param non-empty-string $token Токен загруженного файла.
     * @return $this
     */
    public function addFileAttachment(string $token): self
    {
        $this->data['attachments'][] = new FileAttachmentRequest(new UploadedInfo($token));

        return $this;
    }

    /**
     * Прикрепляет изображение к сообщению.
     *
     * @param non-empty-string $token Токен существующего вложения.
     * @return $this
     */
    public function addImageAttachment(string $token): self
    {
        $this->data['attachments'][] = new PhotoAttachmentRequest(new PhotoAttachmentRequestPayload(token: $token));

        return $this;
    }

    /**
     * Прикрепляет встроенную клавиатуру к сообщению и возвращает объект для наполнения клавиатуры кнопками.
     *
     * Вы можете подключить к чат-боту в МАКС inline-клавиатуру. Она позволяет разместить под сообщением бота
     * до 210 кнопок, сгруппированных в 30 рядов — до 7 кнопок в каждом (до 3, если это кнопки типа `link`,
     * `open_app`, `request_geo_location` или `request_contact`).
     */
    public function addInlineKeyboard(): InlineKeyboardAttachmentRequestPayload
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();
        $this->data['attachments'][] = new InlineKeyboardAttachmentRequest($payload);

        return $payload;
    }

    /**
     * Прикрепляет встроенную клавиатуру к сообщению.
     *
     * Вы можете подключить к чат-боту в МАКС inline-клавиатуру. Она позволяет разместить под сообщением бота
     * до 210 кнопок, сгруппированных в 30 рядов — до 7 кнопок в каждом (до 3, если это кнопки типа `link`,
     * `open_app`, `request_geo_location` или `request_contact`).
     *
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок.
     * @return $this
     */
    public function addInlineKeyboardAttachment(array $buttons): self
    {
        $this->data['attachments'][] = new InlineKeyboardAttachmentRequest(
            new InlineKeyboardAttachmentRequestPayload($buttons),
        );

        return $this;
    }

    /**
     * Добавляет строку к тексту сообщения.
     *
     * @param string $appendedText Добавляемый текст (maxLength: 4000).
     * @return $this
     */
    public function addLine(string $appendedText): self
    {
        $text = $this->data['text'] ?? '';

        $text = $text === ''
            ? $appendedText
            : "$text\n$appendedText";

        return $this->setText($text);
    }

    /**
     * Прикрепляет координаты локации к сообщению.
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
     * Добавляет превью указанного адреса в сообщение.
     *
     * @param non-empty-string $url Адрес, для которого нужно добавить превью в сообщение.
     * @return $this
     */
    public function addShareAttachment(string $url): self
    {
        $this->data['attachments'][] = new ShareAttachmentRequest(new ShareAttachmentPayload($url));

        return $this;
    }

    /**
     * Прикрепляет стикер к сообщению.
     *
     * В одном запросе нельзя отправить больше одного стикера.
     * Стикер нельзя сочетать с другими вложениями, кроме клавиатуры.
     *
     * @param non-empty-string $code Код стикера.
     * @return $this
     */
    public function addStickerAttachment(string $code): self
    {
        $this->data['attachments'][] = new StickerAttachmentRequest(new StickerAttachmentRequestPayload($code));

        return $this;
    }

    /**
     * Добавляет текст к сообщению.
     *
     * @param string $appendedText Добавляемый текст (maxLength: 4000).
     * @return $this
     */
    public function addText(string $appendedText): self
    {
        return $this->setText(($this->data['text'] ?? '') . $appendedText);
    }

    /**
     * Прикрепляет изображение к сообщению.
     *
     * @param non-empty-string $url Любой внешний URL изображения, которое вы хотите прикрепить
     * @return $this
     */
    public function addUrlImageAttachment(string $url): self
    {
        $this->data['attachments'][] = new PhotoAttachmentRequest(new PhotoAttachmentRequestPayload(url: $url));

        return $this;
    }

    /**
     * Прикрепляет видео к сообщению.
     *
     * @param non-empty-string $token Токен загруженного видео.
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

    public function getFormat(): ?TextFormat
    {
        return $this->data['format'] ?? null;
    }

    public function getLink(): ?NewMessageLink
    {
        return $this->data['link'] ?? null;
    }

    public function getNotify(): bool
    {
        return $this->data['notify'] ?? true;
    }

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
     * @param string|null $text Текст сообщения (maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если поле не передано или равно `null`,
     *     изменений не произойдёт. Если массив пуст, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение в чате или пост в канале.
     *     Ссылки на комментарии к постам в каналах не поддерживаются.
     * @param bool $notify Если `false`, участники чата не получат push-уведомления (по умолчанию `true`).
     *     Для каналов нужно передавать `true` или не указывать параметр: каналы не подразумевают
     *     отправку постов без push-уведомлений.
     * @param TextFormat|null $format Разметка текста сообщения. Подробнее —
     *     {@link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting в разделе «Форматирование»}.
     */
    public static function make(
        ?string $text = null,
        ?array $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null,
    ): self {
        return new self($text, $attachments, $link, $notify, $format);
    }

    /**
     * @param string|null $text Текст сообщения (maxLength: 4000).
     * @param AttachmentRequest[]|null $attachments Вложения сообщения. Если поле не передано или равно `null`,
     *     изменений не произойдёт. Если массив пуст, все вложения будут удалены.
     * @param NewMessageLink|null $link Ссылка на сообщение в чате или пост в канале.
     *     Ссылки на комментарии к постам в каналах не поддерживаются.
     * @param bool $notify Если `false`, участники чата не получат push-уведомления (по умолчанию `true`).
     *     Для каналов нужно передавать `true` или не указывать параметр: каналы не подразумевают
     *     отправку постов без push-уведомлений.
     * @param TextFormat|null $format Разметка текста сообщения. Подробнее —
     *     {@link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting в разделе «Форматирование»}.
     */
    public static function new(
        ?string $text = null,
        ?array $attachments = null,
        ?NewMessageLink $link = null,
        bool $notify = true,
        ?TextFormat $format = null,
    ): self {
        return new self($text, $attachments, $link, $notify, $format);
    }

    /**
     * @param AttachmentRequest[] $attachments Вложения сообщения. Если массив пуст, все вложения будут удалены.
     * @return $this
     */
    public function setAttachments(array $attachments): self
    {
        $this->data['attachments'] = array_values($attachments);

        return $this;
    }

    /**
     * @param TextFormat $format Разметка текста сообщения. Подробнее —
     *     {@link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting в разделе «Форматирование»}.
     * @return $this
     */
    public function setFormat(TextFormat $format): self
    {
        $this->data['format'] = $format;

        return $this;
    }

    /**
     * @param non-empty-string|Message|MessageBody $mid Уникальный идентификатор сообщения или объект сообщения.
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
     * @param NewMessageLink $link Ссылка на сообщение в чате или пост в канале.
     *     Ссылки на комментарии к постам в каналах не поддерживаются.
     * @return $this
     */
    public function setLink(NewMessageLink $link): self
    {
        $this->data['link'] = $link;

        return $this;
    }

    /**
     * @param bool $notify Если `false`, участники чата не получат push-уведомления (по умолчанию `true`).
     *     Для каналов нужно передавать `true` или не указывать параметр: каналы не подразумевают
     *     отправку постов без push-уведомлений. При отправке в канал с `false` Вы получите ошибку
     *     {@see BadRequestException} с сообщением `errors.send-message.channel-notify`.
     * @return $this
     */
    public function setNotify(bool $notify): self
    {
        $this->data['notify'] = $notify;

        return $this;
    }

    /**
     * @param non-empty-string|Message|MessageBody $mid Уникальный идентификатор сообщения или объект сообщения.
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
     * @param string $text Текст сообщения (maxLength: 4000).
     * @return $this
     */
    public function setText(string $text): self
    {
        self::validateString('text', $text, maxLength: 4000);

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
