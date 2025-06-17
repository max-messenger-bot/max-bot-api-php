<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * You will get this `update` as soon as message is created.
 *
 * @api
 */
class MessageCreatedUpdate extends Update
{
    /**
     * @var array{
     *     message: array,
     *     user_locale?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false $message = false;

    /**
     * @return Message Newly created message.
     * @api
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? $this->message = Message::newFromData($this->data['message'])
            : $this->message;
    }

    /**
     * @return string|null Current user locale in IETF BCP 47 format. Available only in dialogs.
     * @api
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
