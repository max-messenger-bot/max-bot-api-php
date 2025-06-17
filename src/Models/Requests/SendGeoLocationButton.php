<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ReplyButtonType;

/**
 * Кнопка отправки геолокации.
 *
 * После нажатия на такую кнопку, клиент отправляет новое сообщение с вложением
 * текущего географического положения пользователя.
 */
final class SendGeoLocationButton extends ReplyButton
{
    /**
     * @var array{
     *     quick: bool
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param bool $quick Если `true`, отправляет местоположение без запроса подтверждения пользователя.
     */
    public function __construct(?string $text = null, bool $quick = false)
    {
        $this->required = ['text'];

        parent::__construct(ReplyButtonType::UserGeoLocation, $text);

        $this->setQuick($quick);
    }

    public function isQuick(): bool
    {
        return $this->data['quick'];
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param bool $quick Если `true`, отправляет местоположение без запроса подтверждения пользователя.
     */
    public static function make(string $text, bool $quick = false): self
    {
        return new self($text, $quick);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param bool $quick Если `true`, отправляет местоположение без запроса подтверждения пользователя.
     */
    public static function new(?string $text = null, bool $quick = false): self
    {
        return new self($text, $quick);
    }

    /**
     * @param bool $quick Если `true`, отправляет местоположение без запроса подтверждения пользователя.
     * @return $this
     */
    public function setQuick(bool $quick): self
    {
        $this->data['quick'] = $quick;

        return $this;
    }
}
