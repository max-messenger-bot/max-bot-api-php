<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * You will get this `update` as soon as user presses button.
 *
 * @api
 */
class MessageCallbackUpdate extends Update
{
    /**
     * @var array{
     *     callback: array,
     *     message: array|null,
     *     user_locale?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Callback|false $callback = false;
    private Message|false|null $message = false;

    /**
     * @return Callback Callback data.
     * @api
     */
    public function getCallback(): Callback
    {
        return $this->callback === false
            ? $this->callback = Callback::newFromData($this->data['callback'])
            : $this->callback;
    }

    /**
     * @return Message|null Original message containing inline keyboard.
     *     Can be `null` in case it had been deleted by the moment a bot got this update.
     * @api
     */
    public function getMessage(): ?Message
    {
        return $this->message === false
            ? ($this->message = Message::newFromNullableData($this->data['message'] ?? null))
            : $this->message;
    }

    /**
     * @return string|null Current user locale in IETF BCP 47 format.
     * @api
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
