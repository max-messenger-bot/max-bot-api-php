<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\Validation\KeyboardException;
use MaxMessenger\Bot\Models\Enums\Intent;

use function array_key_exists;
use function count;

/**
 * Запрос на прикрепление клавиатуры к сообщению.
 *
 * Вы можете подключить к чат-боту в MAX inline-клавиатуру. Она позволяет разместить под сообщением бота до 210 кнопок,
 * сгруппированных в 30 рядов — до 7 кнопок в каждом (до 3, если это кнопки типа `link`, `open_app`,
 * `request_geo_location` или `request_contact`).
 */
final class InlineKeyboardAttachmentRequestPayload extends BaseRequestModel
{
    public const MAX_ROWS = 30;
    public const MAX_BUTTONS_IN_ROW = 7;

    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     buttons: non-empty-list<non-empty-list<Button>>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];
    private bool $newRow = false;

    /**
     * @param non-empty-array<non-empty-array<Button>>|null $buttons Двумерный массив кнопок (minItems: 1).
     */
    public function __construct(?array $buttons = null)
    {
        $this->required = ['buttons'];

        if ($buttons !== null) {
            $this->setButtons($buttons);
        }
    }

    public function addButton(Button $button): void
    {
        if (isset($this->data['buttons'])) {
            if ($this->newRow) {
                if (count($this->data['buttons']) >= self::MAX_ROWS) {
                    throw new KeyboardException('Cannot add more rows.');
                }

                $this->data['buttons'][] = [$button];
                $this->newRow = false;
            } else {
                /** @psalm-suppress UnsupportedReferenceUsage */
                $lastRow = &$this->data['buttons'][array_key_last($this->data['buttons'])];
                if (count($lastRow) >= self::MAX_BUTTONS_IN_ROW) {
                    throw new KeyboardException('Cannot add more buttons to this row.');
                }

                $lastRow[] = $button;
            }
        } else {
            $this->data['buttons'] = [[$button]];
            $this->newRow = false;
        }
    }

    /**
     * Добавить Callback-кнопку.
     *
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @param Intent|null $intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public function addCallbackButton(string $text, string $payload, ?Intent $intent = null): self
    {
        $this->addButton(CallbackButton::make($text, $payload, $intent));

        return $this;
    }

    /**
     * Добавить кнопку создания чата.
     *
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $chatTitle Название чата, который будет создан (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $chatDescription Описание чата (minLength: 1, maxLength: 400).
     * @param non-empty-string|null $startPayload Стартовая полезная нагрузка будет отправлена боту,
     *     как только чат будет создан (minLength: 1, maxLength: 512).
     * @param int|null $uuid Уникальный ID кнопки среди всех кнопок чата на клавиатуре.
     */
    public function addChatButton(
        string $text,
        string $chatTitle,
        ?string $chatDescription = null,
        ?string $startPayload = null,
        ?int $uuid = null
    ): self {
        $this->addButton(ChatButton::make($text, $chatTitle, $chatDescription, $startPayload, $uuid));

        return $this;
    }

    /**
     * Добавить кнопку буфера обмена.
     *
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $payload Текст для буфера обмена (minLength: 1, maxLength: 1024).
     */
    public function addClipboardButton(string $text, string $payload): self
    {
        $this->addButton(ClipboardButton::make($text, $payload));

        return $this;
    }

    /**
     * Добавить Кнопку-ссылку.
     *
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $url URL кнопки (minLength: 4, maxLength: 2048).
     */
    public function addLinkButton(string $text, string $url): self
    {
        $this->addButton(LinkButton::make($text, $url));

        return $this;
    }

    /**
     * Добавить кнопку сообщения.
     *
     * @param non-empty-string $text Текст кнопки, который будет отправлен в чат от лица пользователя
     *     (minLength: 1, maxLength: 128).
     */
    public function addMessageButton(string $text): self
    {
        $this->addButton(MessageButton::make($text));

        return $this;
    }

    /**
     * Добавить кнопку запуска мини-приложения.
     *
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $webApp Публичное имя (username) бота или ссылка на него,
     *     чьё мини-приложение надо запустить (minLength: 5).
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в initData
     *     мини-приложения (minLength: 1).
     */
    public function addOpenAppButton(
        string $text,
        ?string $webApp = null,
        ?int $contactId = null,
        ?string $payload = null
    ): self {
        $this->addButton(OpenAppButton::make($text, $webApp, $contactId, $payload));

        return $this;
    }

    /**
     * Добавить кнопку запроса контакта.
     *
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     */
    public function addRequestContactButton(string $text): self
    {
        $this->addButton(RequestContactButton::make($text));

        return $this;
    }

    /**
     * Добавить кнопку запроса геолокации.
     *
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param bool $quick Если `true`, отправляет местоположение без запроса подтверждения пользователя.
     */
    public function addRequestGeoLocationButton(string $text, bool $quick = false): self
    {
        $this->addButton(RequestGeoLocationButton::make($text, $quick));

        return $this;
    }

    /**
     * @return list<list<Button>>
     */
    public function getButtons(): array
    {
        return $this->data['buttons'];
    }

    public function issetButtons(): bool
    {
        return array_key_exists('buttons', $this->data);
    }

    /**
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок (minItems: 1).
     */
    public static function make(array $buttons): self
    {
        return new self($buttons);
    }

    /**
     * @param non-empty-array<non-empty-array<Button>>|null $buttons Двумерный массив кнопок (minItems: 1).
     */
    public static function new(?array $buttons = null): self
    {
        return new self($buttons);
    }

    /**
     * Добавить следующую кнопку в новый ряд.
     *
     * @return $this
     */
    public function newRow(): self
    {
        $this->newRow = true;

        return $this;
    }

    /**
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок (minItems: 1).
     * @return $this
     */
    public function setButtons(array $buttons): self
    {
        self::validateArray('buttons', $buttons, minItems: 1, maxItems: self::MAX_ROWS);
        self::validateArray2D('buttons', $buttons, minItems: 1, maxItems: self::MAX_BUTTONS_IN_ROW);

        foreach ($buttons as &$button) {
            $button = array_values($button);
        }
        /** @var non-empty-array<non-empty-list<Button>> $buttons */

        $this->data['buttons'] = array_values($buttons);

        return $this;
    }
}
