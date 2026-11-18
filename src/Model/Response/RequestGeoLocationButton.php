<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Кнопка запроса геолокации.
 *
 * После нажатия на такую кнопку клиент отправляет новое сообщение с вложением
 * текущего географического положения пользователя.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
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
     *     Если `false`, возвращает модальное окно с дополнительным уточнением об отправке местоположения.
     */
    public function isQuick(): bool
    {
        return $this->data['quick'] ?? false;
    }
}
