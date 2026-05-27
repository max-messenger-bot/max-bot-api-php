<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вы получите это событие, как только пользователь нажмёт в сообщении на
 * кнопку с типом `chat` и будет создан новый чат с ботом, связанный с этим
 * сообщением с кнопкой.
 */
class MessageCallbackUpdate extends Update
{
    /**
     * @var array{
     *     callback: array,
     *     message: array,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Callback|false $callback = false;
    private Message|false $message = false;

    /**
     * @return Callback Объект, отправленный боту, когда пользователь нажал кнопку.
     */
    public function getCallback(): Callback
    {
        return $this->callback === false
            ? $this->callback = Callback::newFromData($this->data['callback'])
            : $this->callback;
    }

    /**
     * @return Message Изначальное сообщение, содержащее встроенную клавиатуру.
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? ($this->message = Message::newFromData($this->data['message']))
            : $this->message;
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47 (minLength: 1).
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
