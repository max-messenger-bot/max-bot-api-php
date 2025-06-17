<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ButtonType;
use MaxMessenger\Bot\Models\Enums\Intent;

use function array_key_exists;

/**
 * Callback-кнопка.
 *
 * После нажатия на такую кнопку клиент отправляет на сервер полезную нагрузку, которая содержит.
 */
final class CallbackButton extends Button
{
    /**
     * @var array{
     *     payload: non-empty-string,
     *     intent?: Intent
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @param Intent|null $intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public function __construct(?string $text = null, ?string $payload = null, ?Intent $intent = null)
    {
        $this->required = ['payload'];

        parent::__construct(ButtonType::Callback, $text);

        if ($payload !== null) {
            $this->setPayload($payload);
        }
        if ($intent !== null) {
            $this->setIntent($intent);
        }
    }

    public function getIntent(): ?Intent
    {
        return $this->data['intent'] ?? null;
    }

    public function getPayload(): string
    {
        return $this->data['payload'];
    }

    public function issetIntent(): bool
    {
        return array_key_exists('intent', $this->data);
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @param Intent|null $intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public static function make(string $text, string $payload, ?Intent $intent = null): self
    {
        return new self($text, $payload, $intent);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @param Intent|null $intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public static function new(?string $text = null, ?string $payload = null, ?Intent $intent = null): self
    {
        return new self($text, $payload, $intent);
    }

    /**
     * @param Intent $intent Намерение кнопки. Влияет на представление в клиентах.
     * @return $this
     */
    public function setIntent(Intent $intent): self
    {
        $this->data['intent'] = $intent;

        return $this;
    }

    /**
     * @param non-empty-string $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @return $this
     */
    public function setPayload(string $payload): self
    {
        self::validateString('payload', $payload, minLength: 1, maxLength: 1024);

        $this->data['payload'] = $payload;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetIntent(): self
    {
        unset($this->data['intent']);

        return $this;
    }
}
