<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use function array_key_exists;

/**
 * Ответ бота на нажатие кнопки.
 *
 * Необходимо указать хотя бы одно из полей: `message` или `notification`.
 *
 * Поле `notification` удалено из официальной документации API, но сервер его по-прежнему принимает.
 *
 * В отличие от обычного редактирования сообщения, при передаче `message` сервер автоматически удаляет
 * вложения, включая клавиатуру, если они не переданы в обновлённом сообщении.
 *
 * @link https://dev.max.ru/docs-api/methods/POST/answers/
 */
final class CallbackAnswer extends BaseRequestModel
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     message?: NewMessageBody,
     *     notification?: string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     * @param string|null $notification Одноразовое уведомление для пользователя.
     */
    public function __construct(?NewMessageBody $message = null, ?string $notification = null)
    {
        $this->requiredOnce = ['message', 'notification'];

        if ($message !== null) {
            $this->setMessage($message);
        }
        if ($notification !== null) {
            $this->setNotification($notification);
        }
    }

    public function getMessage(): ?NewMessageBody
    {
        return $this->data['message'] ?? null;
    }

    public function getNotification(): ?string
    {
        return $this->data['notification'] ?? null;
    }

    public function issetMessage(): bool
    {
        return array_key_exists('message', $this->data);
    }

    public function issetNotification(): bool
    {
        return array_key_exists('notification', $this->data);
    }

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     * @param string|null $notification Одноразовое уведомление для пользователя.
     */
    public static function make(?NewMessageBody $message = null, ?string $notification = null): self
    {
        return new self($message, $notification);
    }

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     * @param string|null $notification Одноразовое уведомление для пользователя.
     */
    public static function new(?NewMessageBody $message = null, ?string $notification = null): self
    {
        return new self($message, $notification);
    }

    /**
     * @param NewMessageBody $message Данные для обновления текущего сообщения.
     * @return $this
     */
    public function setMessage(NewMessageBody $message): self
    {
        $this->data['message'] = $message;

        return $this;
    }

    /**
     * @param string $notification Одноразовое уведомление для пользователя.
     * @return $this
     */
    public function setNotification(string $notification): self
    {
        $this->data['notification'] = $notification;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetMessage(): self
    {
        unset($this->data['message']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetNotification(): self
    {
        unset($this->data['notification']);

        return $this;
    }
}
