<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\RequireArgumentException;

/**
 * @template-extends BaseRequestModel<array{
 *     name: non-empty-string,
 *     description?: non-empty-string|null
 * }>
 * @api
 */
class BotCommand extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @param non-empty-string $name Command name (maxLength: 64).
     * @param non-empty-string|null $description Optional command description (maxLength: 128).
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
        return isset($this->data['description']);
    }

    /**
     * @param non-empty-string|null $name Command name (maxLength: 64)
     * @psalm-param non-empty-string $name
     * @api
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public static function new(string $name = null): static
    {
        if ($name === null) {
            throw new RequireArgumentException('name');
        }

        return new static($name);
    }

    /**
     * @param non-empty-string|null $description Optional command description (maxLength: 128).
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
     * @param non-empty-string $name Command name (maxLength: 64).
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
     * @api
     */
    public function unsetDescription(): static
    {
        unset($this->data['description']);

        return $this;
    }
}
