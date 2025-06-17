<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ButtonType;

use function array_key_exists;

/**
 * Кнопка-ссылка.
 *
 * После нажатия на такую кнопку пользователь переходит по ссылке, которую она содержит.
 */
final class LinkButton extends Button
{
    /**
     * @var array{
     *     url: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $url URL кнопки (minLength: 4, maxLength: 2048).
     */
    public function __construct(?string $text = null, ?string $url = null)
    {
        $this->required = ['text', 'url'];

        parent::__construct(ButtonType::Link, $text);

        if ($url !== null) {
            $this->setUrl($url);
        }
    }

    /**
     * @return non-empty-string
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    public function issetUrl(): bool
    {
        return array_key_exists('url', $this->data);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string $url URL кнопки (minLength: 4, maxLength: 2048).
     */
    public static function make(string $text, string $url): self
    {
        return new self($text, $url);
    }

    /**
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128).
     * @param non-empty-string|null $url URL кнопки (minLength: 4, maxLength: 2048).
     */
    public static function new(?string $text = null, ?string $url = null): self
    {
        return new self($text, $url);
    }

    /**
     * @param non-empty-string $url URL кнопки (minLength: 4, maxLength: 2048).
     * @return $this
     */
    public function setUrl(string $url): self
    {
        self::validateString('url', $url, minLength: 4, maxLength: 2048);

        $this->data['url'] = $url;

        return $this;
    }
}
