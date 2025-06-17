<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ButtonType;

use function array_key_exists;

/**
 * Кнопка для запуска мини-приложения.
 */
final class OpenAppButton extends Button
{
    /**
     * @var array{
     *     web_app?: non-empty-string,
     *     contact_id?: int,
     *     payload?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $webApp Публичное имя (username) бота или ссылка на него,
     *     чьё мини-приложение надо запустить (minLength: 5).
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в initData
     *     мини-приложения (minLength: 1).
     */
    public function __construct(
        ?string $text = null,
        ?string $webApp = null,
        ?int $contactId = null,
        ?string $payload = null
    ) {
        $this->required = ['text'];

        parent::__construct(ButtonType::OpenApp, $text);

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

    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return non-empty-string|null
     */
    public function getWebApp(): ?string
    {
        return $this->data['web_app'] ?? null;
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
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $webApp Публичное имя (username) бота или ссылка на него,
     *     чьё мини-приложение надо запустить (minLength: 5).
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в initData
     *     мини-приложения (minLength: 1).
     */
    public static function make(
        string $text,
        ?string $webApp = null,
        ?int $contactId = null,
        ?string $payload = null
    ): self {
        return new self($text, $webApp, $contactId, $payload);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $webApp Публичное имя (username) бота или ссылка на него,
     *     чьё мини-приложение надо запустить (minLength: 5).
     * @param int|null $contactId Идентификатор бота, чьё мини-приложение надо запустить.
     * @param non-empty-string|null $payload Параметр запуска, который будет передан в initData
     *     мини-приложения (minLength: 1).
     */
    public static function new(
        ?string $text = null,
        ?string $webApp = null,
        ?int $contactId = null,
        ?string $payload = null
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
     * @param non-empty-string $payload Параметр запуска, который будет передан в initData мини-приложения
     *     (minLength: 1).
     * @return $this
     */
    public function setPayload(string $payload): self
    {
        self::validateString('payload', $payload, minLength: 1);

        $this->data['payload'] = $payload;

        return $this;
    }

    /**
     * @param non-empty-string $webApp Публичное имя (username) бота или ссылка на него,
     *     чьё мини-приложение надо запустить (minLength: 5).
     * @return $this
     */
    public function setWebApp(string $webApp): self
    {
        self::validateString('webApp', $webApp, minLength: 5);

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

    /**
     * @return $this
     */
    public function unsetWebApp(): self
    {
        unset($this->data['web_app']);

        return $this;
    }
}
