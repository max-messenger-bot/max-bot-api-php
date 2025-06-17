<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\Intent;

/**
 * Callback-кнопка.
 *
 * После нажатия на такую кнопку клиент отправляет на сервер полезную нагрузку, которая содержит.
 */
class CallbackButton extends Button
{
    /**
     * @var array{
     *     payload: non-empty-string,
     *     intent?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return Intent Намерение кнопки. Влияет на представление в клиентах.
     */
    public function getIntent(): Intent
    {
        return Intent::from($this->data['intent'] ?? 'default');
    }

    /**
     * @return string Намерение кнопки. Влияет на представление в клиентах.
     */
    public function getIntentRaw(): string
    {
        return $this->data['intent'] ?? 'default';
    }

    /**
     * @return non-empty-string Токен кнопки (minLength: 1, maxLength: 1024).
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }
}
