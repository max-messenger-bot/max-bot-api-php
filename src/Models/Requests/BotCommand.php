<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Команда бота
 */
final class BotCommand extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     name: non-empty-string,
     *     description?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $name Название команды (minLength: 1, maxLength: 64).
     * @param non-empty-string|null $description Описание команды (minLength: 1, maxLength: 128).
     */
    public function __construct(?string $name = null, ?string $description = null)
    {
        $this->required = ['name'];

        if ($name !== null) {
            $this->setName($name);
        }
        if ($description !== null) {
            $this->setDescription($description);
        }
    }

    /**
     * @return non-empty-string|null
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    public function issetDescription(): bool
    {
        return array_key_exists('description', $this->data);
    }

    public function issetName(): bool
    {
        return array_key_exists('name', $this->data);
    }

    /**
     * @param non-empty-string $name Название команды (minLength: 1, maxLength: 64).
     * @param non-empty-string|null $description Описание команды (minLength: 1, maxLength: 128).
     */
    public static function make(string $name, ?string $description = null): self
    {
        return new self($name, $description);
    }

    /**
     * @param non-empty-string|null $name Название команды (minLength: 1, maxLength: 64).
     * @param non-empty-string|null $description Описание команды (minLength: 1, maxLength: 128).
     */
    public static function new(?string $name = null, ?string $description = null): self
    {
        return new self($name, $description);
    }

    /**
     * @param non-empty-string $description Описание команды (minLength: 1, maxLength: 128).
     * @return $this
     */
    public function setDescription(string $description): self
    {
        self::validateString('description', $description, minLength: 1, maxLength: 128);

        $this->data['description'] = $description;

        return $this;
    }

    /**
     * @param non-empty-string $name Название команды (minLength: 1, maxLength: 64).
     * @return $this
     */
    public function setName(string $name): self
    {
        self::validateString('name', $name, minLength: 1, maxLength: 64);

        $this->data['name'] = $name;

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
}
