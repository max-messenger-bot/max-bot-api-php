<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\Intent;

/**
 * Send message reply button.
 *
 * After pressing this type of button client will send a message on behalf of user with given payload.
 *
 * @api
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
}
