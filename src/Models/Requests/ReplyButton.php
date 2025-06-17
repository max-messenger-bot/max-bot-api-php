<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ReplyButtonType;

/**
 * Базовый класс кнопки ответа.
 *
 * После нажатия на такую кнопку, клиент отправляет сообщение от имени пользователя с заданным payload.
 *
 * @deprecated В текущей версии API не используется.
 */
abstract class ReplyButton extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     type: ReplyButtonType,
     *     text: non-empty-string,
     *     payload?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param ReplyButtonType $type Тип кнопки.
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Токен кнопки (minLength: 1).
     */
    public function __construct(ReplyButtonType $type, ?string $text = null, ?string $payload = null)
    {
        $this->required[] = 'text';

        $this->data['type'] = $type;
        if ($text !== null) {
            $this->setText($text);
        }
        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return non-empty-string
     */
    public function getText(): string
    {
        return $this->data['text'];
    }

    /**
     * @return ReplyButtonType
     */
    public function getType(): ReplyButtonType
    {
        return $this->data['type'];
    }

    public function issetPayload(): bool
    {
        return isset($this->data['payload']);
    }

    /**
     * @param non-empty-string $payload Токен кнопки (minLength: 1, maxLength: 1024).
     * @return $this
     */
    public function setPayload(string $payload): static
    {
        self::validateString('payload', $payload, minLength: 1, maxLength: 1024);
        $this->data['payload'] = $payload;

        return $this;
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @return $this
     */
    public function setText(string $text): static
    {
        self::validateString('text', $text, minLength: 1, maxLength: 128);
        $this->data['text'] = $text;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetPayload(): static
    {
        unset($this->data['payload']);

        return $this;
    }
}
