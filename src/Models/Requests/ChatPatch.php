<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\MaxApiClient;

use function array_key_exists;

/**
 * Запрос на редактирование информации о чате.
 */
final class ChatPatch extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     icon?: PhotoAttachmentRequestPayload,
     *     title?: non-empty-string,
     *     pin?: non-empty-string,
     *     notify: bool
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Запрос на установку иконки чата.
     * @param non-empty-string|null $title Название чата (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $pin ID сообщения для закрепления в чате (minLength: 1).
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @param bool $notify Если `true`, участники получат системное уведомление об изменении.
     */
    public function __construct(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $pin = null,
        bool $notify = true
    ) {
        if ($icon !== null) {
            $this->setIcon($icon);
        }
        if ($title !== null) {
            $this->setTitle($title);
        }
        if ($pin !== null) {
            $this->setPin($pin);
        }
        $this->setNotify($notify);
    }

    public function getIcon(): ?PhotoAttachmentRequestPayload
    {
        return $this->data['icon'] ?? null;
    }

    public function getNotify(): bool
    {
        return $this->data['notify'];
    }

    /**
     * @return non-empty-string|null
     */
    public function getPin(): ?string
    {
        return $this->data['pin'] ?? null;
    }

    /**
     * @return non-empty-string|null
     */
    public function getTitle(): ?string
    {
        return $this->data['title'] ?? null;
    }

    public function issetIcon(): bool
    {
        return array_key_exists('icon', $this->data);
    }

    public function issetPin(): bool
    {
        return array_key_exists('pin', $this->data);
    }

    public function issetTitle(): bool
    {
        return array_key_exists('title', $this->data);
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Запрос на установку иконки чата.
     * @param non-empty-string|null $title Название чата (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $pin ID сообщения для закрепления в чате (minLength: 1).
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @param bool $notify Если `true`, участники получат системное уведомление об изменении.
     */
    public static function make(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $pin = null,
        bool $notify = true
    ): self {
        return new self($icon, $title, $pin, $notify);
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Запрос на установку иконки чата.
     * @param non-empty-string|null $title Название чата (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $pin ID сообщения для закрепления в чате (minLength: 1).
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @param bool $notify Если `true`, участники получат системное уведомление об изменении.
     */
    public static function new(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $pin = null,
        bool $notify = true
    ): self {
        return new self($icon, $title, $pin, $notify);
    }

    /**
     * @param PhotoAttachmentRequestPayload $icon Запрос на установку иконки чата.
     * @return $this
     */
    public function setIcon(PhotoAttachmentRequestPayload $icon): self
    {
        $this->data['icon'] = $icon;

        return $this;
    }

    /**
     * @param bool $notify Если `true`, участники получат системное уведомление об изменении.
     * @return $this
     */
    public function setNotify(bool $notify): self
    {
        $this->data['notify'] = $notify;

        return $this;
    }

    /**
     * @param non-empty-string $pin ID сообщения для закрепления в чате (minLength: 1).
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @return $this
     */
    public function setPin(string $pin): self
    {
        self::validateString('pin', $pin, minLength: 1);

        $this->data['pin'] = $pin;

        return $this;
    }

    /**
     * @param non-empty-string $title Название чата (minLength: 1, maxLength: 200).
     * @return $this
     */
    public function setTitle(string $title): self
    {
        self::validateString('title', $title, minLength: 1, maxLength: 200);

        $this->data['title'] = $title;

        return $this;
    }

    public function unsetIcon(): self
    {
        unset($this->data['icon']);

        return $this;
    }

    public function unsetPin(): self
    {
        unset($this->data['pin']);

        return $this;
    }

    public function unsetTitle(): self
    {
        unset($this->data['title']);

        return $this;
    }
}
