<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Отправьте этот объект, когда ваш бот хочет отреагировать на нажатие кнопки.
 */
final class CallbackAnswer extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     message?: NewMessageBody,
     *     notification?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param NewMessageBody|null $message Заполните это, если хотите изменить текущее сообщение.
     * @param non-empty-string|null $notification Заполните это, если хотите просто отправить одноразовое
     *     уведомление пользователю (minLength: 1).
     */
    public function __construct(
        ?NewMessageBody $message = null,
        ?string $notification = null
    ) {
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

    /**
     * @return non-empty-string|null
     */
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
     * @param NewMessageBody|null $message Заполните это, если хотите изменить текущее сообщение.
     * @param non-empty-string|null $notification Заполните это, если хотите просто отправить одноразовое
     *     уведомление пользователю (minLength: 1).
     */
    public static function make(
        ?NewMessageBody $message = null,
        ?string $notification = null
    ): self {
        return new self($message, $notification);
    }

    /**
     * @param NewMessageBody|null $message Заполните это, если хотите изменить текущее сообщение.
     * @param non-empty-string|null $notification Заполните это, если хотите просто отправить одноразовое
     *     уведомление пользователю (minLength: 1).
     */
    public static function new(
        ?NewMessageBody $message = null,
        ?string $notification = null
    ): self {
        return new self($message, $notification);
    }

    /**
     * @param NewMessageBody $message Заполните это, если хотите изменить текущее сообщение.
     * @return $this
     */
    public function setMessage(NewMessageBody $message): self
    {
        $this->data['message'] = $message;

        return $this;
    }

    /**
     * @param non-empty-string $notification Заполните это, если хотите просто отправить одноразовое
     *     уведомление пользователю (minLength: 1).
     * @return $this
     */
    public function setNotification(string $notification): self
    {
        self::validateString('notification', $notification, minLength: 1);

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
