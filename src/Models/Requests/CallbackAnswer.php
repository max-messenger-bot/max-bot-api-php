<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Отправьте этот объект, когда ваш бот хочет отреагировать на нажатие кнопки.
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
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param NewMessageBody|null $message Заполните это, если хотите изменить текущее сообщение.
     * @param non-empty-string|null $notification Заполните это, если хотите просто отправить одноразовое
     *     уведомление пользователю.
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
     * @param NewMessageBody|null $message Заполните это, если хотите изменить текущее сообщение.
     * @param non-empty-string|null $notification Заполните это, если хотите просто отправить одноразовое
     *     уведомление пользователю.
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
     * @param NewMessageBody|null $message Заполните это, если хотите изменить текущее сообщение.
     * @return $this
     * @api
     */
    public function setMessage(?NewMessageBody $message = null): static
    {
        $this->data['message'] = $message;

        return $this;
    }

    /**
     * @param non-empty-string|null $notification Заполните это, если хотите просто отправить одноразовое
     *     уведомление пользователю.
     * @return $this
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
