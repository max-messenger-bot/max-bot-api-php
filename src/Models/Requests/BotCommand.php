<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Команда бота
 *
 * @api
 */
final class BotCommand extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     name: non-empty-string,
     *     description?: non-empty-string|null
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string $name Название команды (minLength: 1, maxLength: 64).
     * @param non-empty-string|null $description Описание команды (minLength: 1, maxLength: 128).
     * @api
     */
    public function __construct(string $name, ?string $description = null)
    {
        $this->setName($name);
        if ($description !== null) {
            $this->setDescription($description);
        }
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
     * @return non-empty-string
     * @api
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * @api
     */
    public function issetDescription(): bool
    {
        return array_key_exists('description', $this->data);
    }

    /**
     * @param non-empty-string|null $name Название команды (minLength: 1, maxLength: 64).
     * @psalm-param non-empty-string $name
     * @param non-empty-string|null $description Описание команды (minLength: 1, maxLength: 128).
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?string $name = null, ?string $description = null): static
    {
        static::validateNotNull('name', $name);

        return new static($name, $description);
    }

    /**
     * @param non-empty-string|null $description Описание команды (minLength: 1, maxLength: 128).
     * @return $this
     * @api
     */
    public function setDescription(?string $description): static
    {
        static::validateString('description', $description, minLength: 1, maxLength: 128);

        $this->data['description'] = $description;

        return $this;
    }

    /**
     * @param non-empty-string $name Название команды (minLength: 1, maxLength: 64).
     * @return $this
     * @api
     */
    public function setName(string $name): static
    {
        static::validateString('name', $name, minLength: 1, maxLength: 64);

        $this->data['name'] = $name;

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
}
