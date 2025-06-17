<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ButtonType;

use function array_key_exists;

/**
 * Кнопка создания чата.
 *
 * Кнопка, которая создает новый чат, как только первый пользователь на нее нажмёт.
 *
 * Бот будет добавлен в участники чата как администратор.
 *
 * Автор сообщения станет владельцем чата.
 */
final class ChatButton extends Button
{
    /**
     * @var array{
     *     chat_title: non-empty-string,
     *     chat_description?: non-empty-string,
     *     start_payload?: non-empty-string,
     *     uuid?: int
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $chatTitle Название чата, который будет создан (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $chatDescription Описание чата (minLength: 1, maxLength: 400).
     * @param non-empty-string|null $startPayload Стартовая полезная нагрузка будет отправлена боту,
     *     как только чат будет создан (minLength: 1, maxLength: 512).
     * @param int|null $uuid Уникальный ID кнопки среди всех кнопок чата на клавиатуре.
     */
    public function __construct(
        ?string $text = null,
        ?string $chatTitle = null,
        ?string $chatDescription = null,
        ?string $startPayload = null,
        ?int $uuid = null
    ) {
        $this->required = ['chat_title'];

        parent::__construct(ButtonType::Chat, $text);

        if ($chatTitle !== null) {
            $this->setChatTitle($chatTitle);
        }
        if ($chatDescription !== null) {
            $this->setChatDescription($chatDescription);
        }
        if ($startPayload !== null) {
            $this->setStartPayload($startPayload);
        }
        if ($uuid !== null) {
            $this->setUuid($uuid);
        }
    }

    public function getChatDescription(): ?string
    {
        return $this->data['chat_description'] ?? null;
    }

    /**
     * @return non-empty-string
     */
    public function getChatTitle(): string
    {
        return $this->data['chat_title'];
    }

    public function getStartPayload(): ?string
    {
        return $this->data['start_payload'] ?? null;
    }

    public function getUuid(): ?int
    {
        return $this->data['uuid'] ?? null;
    }

    public function issetChatDescription(): bool
    {
        return array_key_exists('chat_description', $this->data);
    }

    public function issetChatTitle(): bool
    {
        return array_key_exists('chat_title', $this->data);
    }

    public function issetStartPayload(): bool
    {
        return array_key_exists('start_payload', $this->data);
    }

    public function issetUuid(): bool
    {
        return array_key_exists('uuid', $this->data);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $chatTitle Название чата, который будет создан (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $chatDescription Описание чата (minLength: 1, maxLength: 400).
     * @param non-empty-string|null $startPayload Стартовая полезная нагрузка будет отправлена боту,
     *     как только чат будет создан (minLength: 1, maxLength: 512).
     * @param int|null $uuid Уникальный ID кнопки среди всех кнопок чата на клавиатуре.
     */
    public static function make(
        string $text,
        string $chatTitle,
        ?string $chatDescription = null,
        ?string $startPayload = null,
        ?int $uuid = null
    ): self {
        return new self($text, $chatTitle, $chatDescription, $startPayload, $uuid);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $chatTitle Название чата, который будет создан (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $chatDescription Описание чата (minLength: 1, maxLength: 400).
     * @param non-empty-string|null $startPayload Стартовая полезная нагрузка будет отправлена боту,
     *     как только чат будет создан (minLength: 1, maxLength: 512).
     * @param int|null $uuid Уникальный ID кнопки среди всех кнопок чата на клавиатуре.
     */
    public static function new(
        ?string $text = null,
        ?string $chatTitle = null,
        ?string $chatDescription = null,
        ?string $startPayload = null,
        ?int $uuid = null
    ): self {
        return new self($text, $chatTitle, $chatDescription, $startPayload, $uuid);
    }

    /**
     * @param non-empty-string $chatDescription Описание чата (minLength: 1, maxLength: 400).
     * @return $this
     */
    public function setChatDescription(string $chatDescription): self
    {
        self::validateString('chatDescription', $chatDescription, minLength: 1, maxLength: 400);

        $this->data['chat_description'] = $chatDescription;

        return $this;
    }

    /**
     * @param non-empty-string $chatTitle Название чата, который будет создан (minLength: 1, maxLength: 200).
     * @return $this
     */
    public function setChatTitle(string $chatTitle): self
    {
        self::validateString('chatTitle', $chatTitle, minLength: 1, maxLength: 200);

        $this->data['chat_title'] = $chatTitle;

        return $this;
    }

    /**
     * @param non-empty-string $startPayload Стартовая полезная нагрузка будет отправлена боту,
     *     как только чат будет создан (minLength: 1, maxLength: 512).
     * @return $this
     */
    public function setStartPayload(string $startPayload): self
    {
        self::validateString('startPayload', $startPayload, minLength: 1, maxLength: 512);

        $this->data['start_payload'] = $startPayload;

        return $this;
    }

    /**
     * @param int $uuid Уникальный ID кнопки среди всех кнопок чата на клавиатуре.
     * @return $this
     */
    public function setUuid(int $uuid): self
    {
        $this->data['uuid'] = $uuid;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetChatDescription(): self
    {
        unset($this->data['chat_description']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetStartPayload(): self
    {
        unset($this->data['start_payload']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetUuid(): self
    {
        unset($this->data['uuid']);

        return $this;
    }
}
