<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

final class BotPatch extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     first_name?: non-empty-string,
     *     last_name?: non-empty-string,
     *     description?: non-empty-string,
     *     commands?: list<BotCommand>,
     *     photo?: PhotoAttachmentRequestPayload
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $firstName Отображаемое имя пользователя или название бота
     *     (minLength: 1, maxLength: 59).
     * @param non-empty-string|null $lastName Отображаемое второе имя бота (minLength: 1, maxLength: 64).
     * @param non-empty-string|null $description Описание бота (minLength: 1, maxLength: 16000).
     * @param BotCommand[]|null $commands Команды, поддерживаемые ботом. Чтобы удалить все команды,
     *     передайте пустой список (maxItems: 32).
     * @param PhotoAttachmentRequestPayload|null $photo Запрос на установку фото бота.
     */
    public function __construct(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $description = null,
        ?array $commands = null,
        ?PhotoAttachmentRequestPayload $photo = null
    ) {
        if ($firstName !== null) {
            $this->setFirstName($firstName);
        }
        if ($lastName !== null) {
            $this->setLastName($lastName);
        }
        if ($description !== null) {
            $this->setDescription($description);
        }
        if ($commands !== null) {
            $this->setCommands($commands);
        }
        if ($photo !== null) {
            $this->setPhoto($photo);
        }
    }

    /**
     * @return list<BotCommand>|null
     */
    public function getCommands(): ?array
    {
        return $this->data['commands'] ?? null;
    }

    /**
     * @return non-empty-string|null
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string|null
     */
    public function getFirstName(): ?string
    {
        return $this->data['first_name'] ?? null;
    }

    /**
     * @return non-empty-string|null
     */
    public function getLastName(): ?string
    {
        return $this->data['last_name'] ?? null;
    }

    /**
     * @return PhotoAttachmentRequestPayload|null
     */
    public function getPhoto(): ?PhotoAttachmentRequestPayload
    {
        return $this->data['photo'] ?? null;
    }

    public function issetCommands(): bool
    {
        return array_key_exists('commands', $this->data);
    }

    public function issetDescription(): bool
    {
        return array_key_exists('description', $this->data);
    }

    public function issetFirstName(): bool
    {
        return array_key_exists('first_name', $this->data);
    }

    public function issetLastName(): bool
    {
        return array_key_exists('last_name', $this->data);
    }

    public function issetPhoto(): bool
    {
        return array_key_exists('photo', $this->data);
    }

    /**
     * @param non-empty-string|null $firstName Отображаемое имя пользователя или название бота
     *     (minLength: 1, maxLength: 59).
     * @param non-empty-string|null $lastName Отображаемое второе имя бота (minLength: 1, maxLength: 64).
     * @param non-empty-string|null $description Описание бота (minLength: 1, maxLength: 16000).
     * @param BotCommand[]|null $commands Команды, поддерживаемые ботом. Чтобы удалить все команды,
     *     передайте пустой список (maxItems: 32).
     * @param PhotoAttachmentRequestPayload|null $photo Запрос на установку фото бота.
     */
    public static function make(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $description = null,
        ?array $commands = null,
        ?PhotoAttachmentRequestPayload $photo = null
    ): self {
        return new self($firstName, $lastName, $description, $commands, $photo);
    }

    /**
     * @param non-empty-string|null $firstName Отображаемое имя пользователя или название бота
     *     (minLength: 1, maxLength: 59).
     * @param non-empty-string|null $lastName Отображаемое второе имя бота (minLength: 1, maxLength: 64).
     * @param non-empty-string|null $description Описание бота (minLength: 1, maxLength: 16000).
     * @param BotCommand[]|null $commands Команды, поддерживаемые ботом. Чтобы удалить все команды,
     *     передайте пустой список (maxItems: 32).
     * @param PhotoAttachmentRequestPayload|null $photo Запрос на установку фото бота.
     */
    public static function new(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $description = null,
        ?array $commands = null,
        ?PhotoAttachmentRequestPayload $photo = null
    ): self {
        return new self($firstName, $lastName, $description, $commands, $photo);
    }

    /**
     * @param BotCommand[] $commands Команды, поддерживаемые ботом. Чтобы удалить все команды,
     *     передайте пустой список (maxItems: 32).
     * @return $this
     */
    public function setCommands(array $commands): self
    {
        self::validateArray('commands', $commands, maxItems: 32);

        $this->data['commands'] = array_values($commands);

        return $this;
    }

    /**
     * @param non-empty-string $description Описание бота (minLength: 1, maxLength: 16000).
     * @return $this
     */
    public function setDescription(string $description): self
    {
        self::validateString('description', $description, minLength: 1, maxLength: 16000);

        $this->data['description'] = $description;

        return $this;
    }

    /**
     * @param non-empty-string $firstName Отображаемое имя пользователя или название бота (minLength: 1, maxLength: 59).
     * @return $this
     */
    public function setFirstName(string $firstName): self
    {
        self::validateString('firstName', $firstName, minLength: 1, maxLength: 59);

        $this->data['first_name'] = $firstName;

        return $this;
    }

    /**
     * @param non-empty-string $lastName Отображаемое второе имя бота (minLength: 1, maxLength: 64).
     * @return $this
     */
    public function setLastName(string $lastName): self
    {
        self::validateString('lastName', $lastName, minLength: 1, maxLength: 64);

        $this->data['last_name'] = $lastName;

        return $this;
    }

    /**
     * @param PhotoAttachmentRequestPayload $photo Запрос на установку фото бота.
     * @return $this
     */
    public function setPhoto(PhotoAttachmentRequestPayload $photo): self
    {
        $this->data['photo'] = $photo;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetCommands(): self
    {
        unset($this->data['commands']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetDescription(): self
    {
        unset($this->data['description']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetFirstName(): self
    {
        unset($this->data['first_name']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetLastName(): self
    {
        unset($this->data['last_name']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetPhoto(): self
    {
        unset($this->data['photo']);

        return $this;
    }
}
