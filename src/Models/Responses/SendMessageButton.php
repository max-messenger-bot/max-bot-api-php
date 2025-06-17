<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\Intent;

/**
 * Кнопка отправки сообщения.
 *
 * Кнопка для запуска мини-приложения.
 */
class SendMessageButton extends ReplyButton
{
    /**
     * @var array{
     *     intent?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return Intent|null Намерение кнопки. Влияет на представление в клиентах.
     */
    public function getIntent(): ?Intent
    {
        return Intent::tryFrom($this->data['intent'] ?? 'default');
    }

    /**
     * @return string|null Намерение кнопки. Влияет на представление в клиентах.
     */
    public function getIntentRaw(): ?string
    {
        return $this->data['intent'] ?? null;
    }
}
