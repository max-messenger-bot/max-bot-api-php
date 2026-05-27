<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\ButtonType;

use function array_key_exists;

/**
 * Кнопка буфера обмена.
 *
 * При нажатии на кнопку с типом `clipboard` текст, указанный в свойстве `payload`, копируется в буфер обмена.
 */
final class ClipboardButton extends Button
{
    /**
     * @var array{
     *     payload: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Текст, который будет скопирован (minLength: 1, maxLength: 1024).
     */
    public function __construct(?string $text = null, ?string $payload = null)
    {
        $this->required = ['payload'];

        parent::__construct(ButtonType::Clipboard, $text);

        if ($payload !== null) {
            $this->setPayload($payload);
        }
    }

    public function getPayload(): string
    {
        return $this->data['payload'];
    }

    public function issetPayload(): bool
    {
        return array_key_exists('payload', $this->data);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $payload Текст, который будет скопирован (minLength: 1, maxLength: 1024).
     */
    public static function make(string $text, string $payload): self
    {
        return new self($text, $payload);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $payload Текст, который будет скопирован (minLength: 1, maxLength: 1024).
     */
    public static function new(?string $text = null, ?string $payload = null): self
    {
        return new self($text, $payload);
    }

    /**
     * @param non-empty-string $payload Текст, который будет скопирован (minLength: 1, maxLength: 1024).
     * @return $this
     */
    public function setPayload(string $payload): self
    {
        self::validateString('payload', $payload, minLength: 1, maxLength: 1024);

        $this->data['payload'] = $payload;

        return $this;
    }
}
