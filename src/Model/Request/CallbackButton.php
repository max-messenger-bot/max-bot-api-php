<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use JsonException;
use MaxMessenger\Bot\Exception\HttpClient\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Enum\Intent;

use function array_key_exists;
use function is_array;
use function json_encode;

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
     * @param non-empty-string|array|null $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @param Intent|null $intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public function __construct(?string $text = null, string|array|null $payload = null, ?Intent $intent = null)
    {
        parent::__construct(ButtonType::Callback, $text);

        $this->required[] = 'payload';

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
     * @param non-empty-string|array $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @param Intent|null $intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public static function make(string $text, string|array $payload, ?Intent $intent = null): self
    {
        return new self($text, $payload, $intent);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|array|null $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @param Intent|null $intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public static function new(?string $text = null, string|array|null $payload = null, ?Intent $intent = null): self
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
     * @param non-empty-string|array $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @return $this
     */
    public function setPayload(string|array $payload): self
    {
        if (is_array($payload)) {
            try {
                $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                /** @psalm-var array $payload */
                throw new JsonEncodeException($payload, $e);
            }
        }

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
