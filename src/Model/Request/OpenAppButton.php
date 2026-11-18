<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\ButtonType;

use function array_key_exists;

/**
 * Кнопка для запуска мини-приложения.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody/
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
final class OpenAppButton extends Button
{
    /**
     * @var array{
     *     web_app: non-empty-string,
     *     contact_id?: int,
     *     payload?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string|null $webApp Публичное имя (username) бота, чьё мини-приложение надо запустить.
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в `initData` мини-приложения
     *     (maxLength: 512, pattern: '^[\w-]*$').
     */
    public function __construct(
        ?string $text = null,
        ?string $webApp = null,
        ?int $contactId = null,
        ?string $payload = null,
    ) {
        parent::__construct(ButtonType::OpenApp, $text);

        $this->required[] = 'web_app';

        if ($webApp !== null) {
            $this->setWebApp($webApp);
        }
        if ($contactId !== null) {
            $this->setContactId($contactId);
        }
        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getContactId(): ?int
    {
        return $this->data['contact_id'] ?? null;
    }

    /**
     * @return non-empty-string|null
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return non-empty-string
     */
    public function getWebApp(): string
    {
        return $this->data['web_app'];
    }

    public function issetContactId(): bool
    {
        return array_key_exists('contact_id', $this->data);
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    public function issetWebApp(): bool
    {
        return array_key_exists('web_app', $this->data);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string $webApp Публичное имя (username) бота, чьё мини-приложение надо запустить.
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в `initData` мини-приложения
     *     (maxLength: 512, pattern: '^[\w-]*$').
     */
    public static function make(
        string $text,
        string $webApp,
        ?int $contactId = null,
        ?string $payload = null,
    ): self {
        return new self($text, $webApp, $contactId, $payload);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (maxLength: 128).
     * @param non-empty-string|null $webApp Публичное имя (username) бота, чьё мини-приложение надо запустить.
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в `initData` мини-приложения
     *     (maxLength: 512, pattern: '^[\w-]*$').
     */
    public static function new(
        ?string $text = null,
        ?string $webApp = null,
        ?int $contactId = null,
        ?string $payload = null,
    ): self {
        return new self($text, $webApp, $contactId, $payload);
    }

    /**
     * @param int $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @return $this
     */
    public function setContactId(int $contactId): self
    {
        $this->data['contact_id'] = $contactId;

        return $this;
    }

    /**
     * @param non-empty-string $payload Параметр запуска, который будет передан в `initData` мини-приложения
     *     (maxLength: 512, pattern: '^[\w-]*$').
     * @return $this
     */
    public function setPayload(string $payload): self
    {
        self::validateString('payload', $payload, minLength: 1, maxLength: 512, pattern: '/^[\w-]*$/');

        $this->data['payload'] = $payload;

        return $this;
    }

    /**
     * @param non-empty-string $webApp Публичное имя (username) бота, чьё мини-приложение надо запустить.
     * @return $this
     */
    public function setWebApp(string $webApp): self
    {
        self::validateString('webApp', $webApp, minLength: 1);

        $this->data['web_app'] = $webApp;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetContactId(): self
    {
        unset($this->data['contact_id']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetPayload(): self
    {
        unset($this->data['payload']);

        return $this;
    }
}
