<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use DateTimeImmutable;

/**
 * Object sent to bot when user presses button.
 *
 * @api
 */
class Callback extends BaseResponseModel
{
    /**
     * @var array{
     *     callback_id: string,
     *     payload?: string,
     *     timestamp: int,
     *     user: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return string Current keyboard identifier.
     * @api
     */
    public function getCallbackId(): string
    {
        return $this->data['callback_id'];
    }

    /**
     * @return string|null Button payload.
     * @api
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return DateTimeImmutable Time when user pressed the button.
     * @api
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['timestamp']);
    }

    /**
     * @return int Unix-time when user pressed the button (Unix timestamp in milliseconds).
     * @api
     */
    public function getTimestampRaw(): int
    {
        return $this->data['timestamp'];
    }

    /**
     * @return User User pressed the button.
     * @api
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }
}
