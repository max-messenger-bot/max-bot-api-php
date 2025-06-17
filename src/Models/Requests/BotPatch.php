<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * @api
 */
final class BotPatch extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     commands?: list<BotCommand>|null,
     *     description?: non-empty-string|null,
     *     first_name?: non-empty-string|null,
     *     photo?: PhotoAttachmentRequestPayload|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param non-empty-string|null $firstName Visible name of bot (minLength: 1, maxLength: 59).
     * @param non-empty-string|null $description Bot description up to 16k characters long
     *     (minLength: 1, maxLength: 16000).
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
        return array_key_exists('commands', $this->data);
    }

    /**
     * @api
     */
    public function issetDescription(): bool
    {
        return array_key_exists('description', $this->data);
    }

    /**
     * @api
     */
    public function issetFirstName(): bool
    {
        return array_key_exists('first_name', $this->data);
    }

    /**
     * @api
     */
    public function issetPhoto(): bool
    {
        return array_key_exists('photo', $this->data);
    }

    /**
     * @param non-empty-string|null $firstName Visible name of bot (minLength: 1, maxLength: 59).
     * @param non-empty-string|null $description Bot description up to 16k characters long
     *     (minLength: 1, maxLength: 16000).
     * @param BotCommand[]|null $commands Commands supported by bot.
     *     Pass empty list if you want to remove commands (maxItems: 32).
     * @param PhotoAttachmentRequestPayload|null $photo Request to set bot photo.
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(
        ?string $firstName = null,
        ?string $description = null,
        ?array $commands = null,
        ?PhotoAttachmentRequestPayload $photo = null
    ): static {
        return new static($firstName, $description, $commands, $photo);
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
     * @return $this
     * @api
     */
    public function unsetCommands(): static
    {
        unset($this->data['commands']);

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetDescription(): static
    {
        unset($this->data['description']);

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetFirstName(): static
    {
        unset($this->data['first_name']);

        return $this;
    }

    /**
     * @return $this
     * @api
     */
    public function unsetPhoto(): static
    {
        unset($this->data['photo']);

        return $this;
    }
}
