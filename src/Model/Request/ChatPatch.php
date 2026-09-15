<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

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
     *     description?: string,
     *     pin?: non-empty-string,
     *     notify: bool
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Данные для прикрепления изображения в качестве аватара
     *     чата или канала.
     * @param non-empty-string|null $title Название чата (maxLength: 200).
     * @param string|null $description Новое описание чата или канала (maxLength: 16000).
     *     Чтобы удалить описание, передайте пустую строку.
     * @param non-empty-string|null $pin ID сообщения для закрепления в чате или канале
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @param bool $notify Если `true`, участники получат системное уведомление об изменении.
     */
    public function __construct(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $description = null,
        ?string $pin = null,
        bool $notify = true,
    ) {
        if ($icon !== null) {
            $this->setIcon($icon);
        }
        if ($title !== null) {
            $this->setTitle($title);
        }
        if ($description !== null) {
            $this->setDescription($description);
        }
        if ($pin !== null) {
            $this->setPin($pin);
        }
        $this->setNotify($notify);
    }

    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
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

    public function issetDescription(): bool
    {
        return array_key_exists('description', $this->data);
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
     * @param PhotoAttachmentRequestPayload|null $icon Данные для прикрепления изображения в качестве аватара
     *     чата или канала.
     * @param non-empty-string|null $title Название чата (maxLength: 200).
     * @param string|null $description Новое описание чата или канала (maxLength: 16000).
     *     Чтобы удалить описание, передайте пустую строку.
     * @param non-empty-string|null $pin ID сообщения для закрепления в чате или канале
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @param bool $notify Если `true`, участники получат системное уведомление об изменении.
     */
    public static function make(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $description = null,
        ?string $pin = null,
        bool $notify = true,
    ): self {
        return new self($icon, $title, $description, $pin, $notify);
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Данные для прикрепления изображения в качестве аватара
     *     чата или канала.
     * @param non-empty-string|null $title Название чата (maxLength: 200).
     * @param string|null $description Новое описание чата или канала (maxLength: 16000).
     *     Чтобы удалить описание, передайте пустую строку.
     * @param non-empty-string|null $pin ID сообщения для закрепления в чате или канале
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @param bool $notify Если `true`, участники получат системное уведомление об изменении.
     */
    public static function new(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $description = null,
        ?string $pin = null,
        bool $notify = true,
    ): self {
        return new self($icon, $title, $description, $pin, $notify);
    }

    /**
     * @param string $description Новое описание чата или канала (maxLength: 16000).
     *     Чтобы удалить описание, передайте пустую строку.
     * @return $this
     */
    public function setDescription(string $description): self
    {
        self::validateString('description', $description, maxLength: 16000);

        $this->data['description'] = $description;

        return $this;
    }

    /**
     * @param PhotoAttachmentRequestPayload $icon Данные для прикрепления изображения в качестве аватара
     *     чата или канала.
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
     * @param non-empty-string $pin ID сообщения для закрепления в чате или канале
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Чтобы удалить закреплённое сообщение, используйте метод {@see MaxApiClient::unpinMessage()}.
     * @return $this
     */
    public function setPin(string $pin): self
    {
        self::validateString('pin', $pin, minLength: 1, pattern: '/^mid\.[a-zA-Z0-9_\-]+$/');

        $this->data['pin'] = $pin;

        return $this;
    }

    /**
     * @param non-empty-string $title Название чата (maxLength: 200).
     * @return $this
     */
    public function setTitle(string $title): self
    {
        self::validateString('title', $title, minLength: 1, maxLength: 200);

        $this->data['title'] = $title;

        return $this;
    }

    public function unsetDescription(): self
    {
        unset($this->data['description']);

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
