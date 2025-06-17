<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Вы получите этот `update`, как только сообщение будет создано.
 */
class MessageCreatedUpdate extends Update
{
    /**
     * @var array{
     *     message: array,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false $message = false;

    /**
     * @return Message Новое созданное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? $this->message = Message::newFromData($this->data['message'])
            : $this->message;
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47 (minLength: 1). Доступно только в диалогах.
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
