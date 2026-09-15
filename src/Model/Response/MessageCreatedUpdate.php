<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вы получите это событие, как только сообщение будет создано.
 *
 * Событие может относиться к объекту, который не поддерживается MAX API.
 * В этом случае {@see getMessage()} вернёт `null`.
 */
class MessageCreatedUpdate extends Update
{
    /**
     * @var array{
     *     message?: array,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false|null $message = false;

    /**
     * @return Message|null Новое созданное сообщение.
     *     `null`, если событие относится к объекту, который не поддерживается MAX API.
     */
    public function getMessage(): ?Message
    {
        return $this->message === false
            ? $this->message = Message::newFromNullableData($this->data['message'] ?? null)
            : $this->message;
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47. Доступно только в диалогах.
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
