<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use function array_key_exists;

/**
 * Отправьте этот объект, когда ваш бот хочет отреагировать на нажатие кнопки.
 */
final class CallbackAnswer extends BaseRequestModel
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     message: NewMessageBody
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     * @param non-empty-string|null $notification Устарело: с 21 июля 2026 г. поле удалено из официальной
     *     схемы API — на сервер не передаётся и игнорируется.
     * @psalm-suppress UnusedParam Параметр сохранён для обратной совместимости сигнатуры.
     */
    public function __construct(
        ?NewMessageBody $message = null,
        ?string $notification = null,
    ) {
        $this->required = ['message'];

        if ($message !== null) {
            $this->setMessage($message);
        }
    }

    public function getMessage(): ?NewMessageBody
    {
        return $this->data['message'] ?? null;
    }

    /**
     * @return null Всегда `null`.
     * @deprecated С 21 июля 2026 г. поле удалено из официальной схемы API — на сервер не передаётся.
     */
    public function getNotification(): null
    {
        return null;
    }

    public function issetMessage(): bool
    {
        return array_key_exists('message', $this->data);
    }

    /**
     * @return false Всегда `false`.
     * @deprecated С 21 июля 2026 г. поле удалено из официальной схемы API — на сервер не передаётся.
     */
    public function issetNotification(): false
    {
        return false;
    }

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     * @param non-empty-string|null $notification Устарело: с 21 июля 2026 г. поле удалено из официальной
     *     схемы API — на сервер не передаётся и игнорируется.
     */
    public static function make(
        ?NewMessageBody $message = null,
        ?string $notification = null,
    ): self {
        return new self($message, $notification);
    }

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     * @param non-empty-string|null $notification Устарело: с 21 июля 2026 г. поле удалено из официальной
     *     схемы API — на сервер не передаётся и игнорируется.
     */
    public static function new(
        ?NewMessageBody $message = null,
        ?string $notification = null,
    ): self {
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
     * @param non-empty-string $notification Устарело: с 21 июля 2026 г. поле удалено из официальной
     *     схемы API — на сервер не передаётся и игнорируется.
     * @return $this
     * @deprecated С 21 июля 2026 г. поле удалено из официальной схемы API — метод ничего не делает.
     * @psalm-suppress UnusedParam Параметр сохранён для обратной совместимости сигнатуры.
     */
    public function setNotification(string $notification): self
    {
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
     * @deprecated С 21 июля 2026 г. поле удалено из официальной схемы API — метод ничего не делает.
     */
    public function unsetNotification(): self
    {
        return $this;
    }
}
