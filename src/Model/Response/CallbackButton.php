<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Callback-кнопка.
 *
 * После нажатия на такую кнопку клиент отправляет на сервер её полезную нагрузку.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
class CallbackButton extends Button
{
    /**
     * @var array{
     *     payload: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Данные кнопки (maxLength: 1024).
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }
}
