<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Кнопка запроса геолокации.
 *
 * После нажатия на такую кнопку клиент отправляет новое сообщение с вложением
 * текущего географического положения пользователя.
 */
class RequestGeoLocationButton extends Button
{
    /**
     * @var array{
     *     quick?: bool
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return bool Если `true`, отправляет местоположение без запроса подтверждения пользователя.
     */
    public function isQuick(): bool
    {
        return $this->data['quick'] ?? false;
    }
}
