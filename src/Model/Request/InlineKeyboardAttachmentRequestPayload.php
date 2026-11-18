<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Exception\Validation\KeyboardException;

use function array_key_exists;
use function array_key_last;
use function array_values;
use function count;

/**
 * Запрос на прикрепление клавиатуры к сообщению.
 *
 * Вы можете подключить к чат-боту в МАКС inline-клавиатуру. Она позволяет разместить под сообщением бота до 210 кнопок,
 * сгруппированных в 30 рядов — до 7 кнопок в каждом (до 3, если это кнопки типа `link`, `open_app`,
 * `request_geo_location` или `request_contact`).
 *
 * Ограничение в 3 кнопки модель не проверяет: при сборке клавиатуры за ним следит вызывающий код.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody/
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
final class InlineKeyboardAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    public const MAX_BUTTONS_IN_ROW = 7;
    public const MAX_ROWS = 30;

    /**
     * @var array{
     *     buttons: non-empty-list<non-empty-list<Button>>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];
    private bool $newRow = false;

    /**
     * @param non-empty-array<non-empty-array<Button>>|null $buttons Двумерный массив кнопок.
     */
    public function __construct(?array $buttons = null)
    {
        $this->required = ['buttons'];

        if ($buttons !== null) {
            $this->setButtons($buttons);
        }
    }

    /**
     * Добавляет кнопку в текущий ряд встроенной клавиатуры.
     *
     * @param Button $button Кнопка для добавления.
     */
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
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string|array $payload Данные кнопки (maxLength: 1024).
     * @return $this
     */
    public function addCallbackButton(string $text, string|array $payload): self
    {
        $this->addButton(CallbackButton::make($text, $payload));

        return $this;
    }

    /**
     * Добавить кнопку буфера обмена.
     *
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string $payload Текст, который копируется в буфер обмена после нажатия на кнопку
     *     (maxLength: 1024).
     * @return $this
     */
    public function addClipboardButton(string $text, string $payload): self
    {
        $this->addButton(ClipboardButton::make($text, $payload));

        return $this;
    }

    /**
     * Добавить Кнопку-ссылку.
     *
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string $url URL кнопки (maxLength: 2048).
     * @return $this
     */
    public function addLinkButton(string $text, string $url): self
    {
        $this->addButton(LinkButton::make($text, $url));

        return $this;
    }

    /**
     * Добавить кнопку сообщения.
     *
     * @param non-empty-string $text Текст кнопки, который будет отправлен в чат от лица пользователя (maxLength: 128).
     * @return $this
     */
    public function addMessageButton(string $text): self
    {
        $this->addButton(MessageButton::make($text));

        return $this;
    }

    /**
     * Добавить кнопку запуска мини-приложения.
     *
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string $webApp Публичное имя (username) бота,
     *     чьё мини-приложение надо запустить.
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в `initData` мини-приложения
     *     (maxLength: 512, pattern: '^[\w-]*$').
     * @return $this
     */
    public function addOpenAppButton(
        string $text,
        string $webApp,
        ?int $contactId = null,
        ?string $payload = null,
    ): self {
        $this->addButton(OpenAppButton::make($text, $webApp, $contactId, $payload));

        return $this;
    }

    /**
     * Добавить кнопку запроса контакта.
     *
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @return $this
     */
    public function addRequestContactButton(string $text): self
    {
        $this->addButton(RequestContactButton::make($text));

        return $this;
    }

    /**
     * Добавить кнопку запроса геолокации.
     *
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @param bool $quick Если `true`, отправляет местоположение без запроса подтверждения пользователя.
     *     Если `false`, возвращает модальное окно с дополнительным уточнением об отправке местоположения.
     * @return $this
     */
    public function addRequestGeoLocationButton(string $text, bool $quick = false): self
    {
        $this->addButton(RequestGeoLocationButton::make($text, $quick));

        return $this;
    }

    /**
     * @return non-empty-list<non-empty-list<Button>>
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
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок.
     */
    public static function make(array $buttons): self
    {
        return new self($buttons);
    }

    /**
     * @param non-empty-array<non-empty-array<Button>>|null $buttons Двумерный массив кнопок.
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
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок.
     * @return $this
     */
    public function setButtons(array $buttons): self
    {
        self::validateArray('buttons', $buttons, minItems: 1, maxItems: self::MAX_ROWS);
        self::validateArray2D('buttons', $buttons, minItems: 1, maxItems: self::MAX_BUTTONS_IN_ROW);

        $rows = [];
        foreach ($buttons as $button) {
            $rows[] = array_values($button);
        }

        $this->data['buttons'] = $rows;

        return $this;
    }
}
