<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Callback-кнопка.
 *
 * После нажатия на такую кнопку клиент отправляет на сервер полезную нагрузку, которая содержит.
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
     * @return non-empty-string Токен кнопки (maxLength: 1024).
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }
}
