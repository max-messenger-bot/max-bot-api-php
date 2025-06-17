<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Send this object when your bot wants to react to when a button is pressed.
 *
 * @api
 */
final class CallbackAnswer extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     message?: NewMessageBody|null,
     *     notification?: non-empty-string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param NewMessageBody|null $message Fill this if you want to modify current message.
     * @param non-empty-string|null $notification Fill this if you just want to send
     *     one-time notification to user (minLength: 1).
     * @api
     */
    public function __construct(
        ?NewMessageBody $message = null,
        ?string $notification = null
    ) {
        if ($message !== null) {
            $this->setMessage($message);
        }
        if ($notification !== null) {
            $this->setNotification($notification);
        }
    }

    /**
     * @api
     */
    public function getMessage(): ?NewMessageBody
    {
        return $this->data['message'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getNotification(): ?string
    {
        return $this->data['notification'] ?? null;
    }

    /**
     * @api
     */
    public function issetMessage(): bool
    {
        return array_key_exists('message', $this->data);
    }

    /**
     * @api
     */
    public function issetNotification(): bool
    {
        return array_key_exists('notification', $this->data);
    }

    /**
     * @param NewMessageBody|null $message Fill this if you want to modify current message.
     * @param non-empty-string|null $notification Fill this if you just want to send
     *     one-time notification to user (minLength: 1).
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(
        ?NewMessageBody $message = null,
        ?string $notification = null
    ): static {
        return new static($message, $notification);
    }

    /**
     * @param NewMessageBody|null $message Fill this if you want to modify current message.
     * @api
     */
    public function setMessage(?NewMessageBody $message = null): static
    {
        $this->data['message'] = $message;

        return $this;
    }

    /**
     * @param non-empty-string|null $notification Fill this if you just want to send
     *     one-time notification to user (minLength: 1).
     * @api
     */
    public function setNotification(?string $notification = null): static
    {
        static::validateString('notification', $notification, minLength: 1);

        $this->data['notification'] = $notification;

        return $this;
    }

    /**
     * @api
     */
    public function unsetMessage(): static
    {
        unset($this->data['message']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetNotification(): static
    {
        unset($this->data['notification']);

        return $this;
    }
}
