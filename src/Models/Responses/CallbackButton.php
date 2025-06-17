<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\Intent;

/**
 * Callback button.
 *
 * After pressing this type of button client sends to server payload it contains.
 *
 * @api
 */
class CallbackButton extends Button
{
    /**
     * @var array{
     *     payload: string,
     *     intent?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return Intent|null Intent of button. Affects clients representation.
     * @api
     */
    public function getIntent(): ?Intent
    {
        return Intent::tryFrom($this->data['intent'] ?? 'default');
    }

    /**
     * @return string|null Intent of button. Affects clients representation.
     * @api
     */
    public function getIntentRaw(): ?string
    {
        return $this->data['intent'] ?? null;
    }

    /**
     * @return string Button payload (maxLength: 1024).
     * @api
     */
    public function getPayload(): string
    {
        return $this->data['payload'];
    }
}
