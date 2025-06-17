<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

/**
 * @template-extends BaseRequestModel<array{
 *     name?: non-empty-string|null,
 *     first_name?: non-empty-string|null,
 *     description?: non-empty-string|null,
 *     commands?: list<BotCommand>|null,
 *     photo?: PhotoAttachmentRequestPayload|null
 * }>
 * @api
 */
class BotPatch extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @param non-empty-string|null $firstName Visible name of bot (maxLength: 59).
     * @param non-empty-string|null $description Bot description up to 16k characters long (maxLength: 16000).
     * @param BotCommand[]|null $commands Commands supported by bot.
     *     Pass empty list if you want to remove commands (maxItems: 32).
     * @param PhotoAttachmentRequestPayload|null $photo Request to set bot photo.
     * @api
     */
    public function __construct(
        ?string $firstName = null,
        ?string $description = null,
        ?array $commands = null,
        ?PhotoAttachmentRequestPayload $photo = null
    ) {
        if ($firstName !== null) {
            $this->setFirstName($firstName);
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
     * @api
     */
    public function getCommands(): ?array
    {
        return $this->data['commands'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getFirstName(): ?string
    {
        return $this->data['first_name'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * @return PhotoAttachmentRequestPayload|null
     * @api
     */
    public function getPhoto(): ?PhotoAttachmentRequestPayload
    {
        return $this->data['photo'] ?? null;
    }

    /**
     * @api
     */
    public function issetCommands(): bool
    {
        return isset($this->data['commands']);
    }

    /**
     * @api
     */
    public function issetDescription(): bool
    {
        return isset($this->data['description']);
    }

    /**
     * @api
     */
    public function issetFirstName(): bool
    {
        return isset($this->data['first_name']);
    }

    /**
     * @api
     */
    public function issetName(): bool
    {
        return isset($this->data['name']);
    }

    /**
     * @api
     */
    public function issetPhoto(): bool
    {
        return isset($this->data['photo']);
    }

    /**
     * @param BotCommand[]|null $commands Commands supported by bot.
     *     Pass empty list if you want to remove commands (maxItems: 32).
     * @return $this
     * @api
     */
    public function setCommands(?array $commands = null): static
    {
        static::validateArray('commands', $commands, maxItems: 32);

        $this->data['commands'] = $commands !== null ? array_values($commands) : null;

        return $this;
    }

    /**
     * @param non-empty-string|null $description Bot description up to 16k characters long (maxLength: 16000).
     * @return $this
     * @api
     */
    public function setDescription(?string $description): static
    {
        static::validateString('description', $description, minLength: 1, maxLength: 16000);

        $this->data['description'] = $description;

        return $this;
    }

    /**
     * @param non-empty-string|null $firstName Visible name of bot (maxLength: 59).
     * @return $this
     * @api
     */
    public function setFirstName(?string $firstName): static
    {
        static::validateString('first_name', $firstName, minLength: 1, maxLength: 59);

        $this->data['first_name'] = $firstName;

        return $this;
    }

    /**
     * @param non-empty-string|null $name Visible name of bot (maxLength: 59).
     * @return $this
     * @deprecated
     * @api
     */
    public function setName(?string $name): static
    {
        static::validateString('name', $name, minLength: 1, maxLength: 59);

        $this->data['name'] = $name;

        return $this;
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $photo Request to set bot photo.
     * @return $this
     * @api
     */
    public function setPhoto(?PhotoAttachmentRequestPayload $photo = null): static
    {
        $this->data['photo'] = $photo;

        return $this;
    }

    /**
     * @api
     */
    public function unsetCommands(): static
    {
        unset($this->data['commands']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetDescription(): static
    {
        unset($this->data['description']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetFirstName(): static
    {
        unset($this->data['first_name']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetName(): static
    {
        unset($this->data['name']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetPhoto(): static
    {
        unset($this->data['photo']);

        return $this;
    }
}
