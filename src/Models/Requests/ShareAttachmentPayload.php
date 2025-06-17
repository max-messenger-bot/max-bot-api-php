<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Полезная нагрузка запроса ShareAttachmentRequest.
 */
final class ShareAttachmentPayload extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     url: non-empty-string,
     *     token?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $url URL, прикрепленный к сообщению в качестве предпросмотра медиа (minLength: 1).
     * @param non-empty-string|null $token Токен вложения (minLength: 1).
     */
    public function __construct(?string $url = null, ?string $token = null)
    {
        $this->required = ['url'];

        if ($url !== null) {
            $this->setUrl($url);
        }
        if ($token !== null) {
            $this->setToken($token);
        }
    }

    /**
     * @return non-empty-string|null
     */
    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    /**
     * @return non-empty-string
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }

    public function issetToken(): bool
    {
        return array_key_exists('token', $this->data);
    }

    public function issetUrl(): bool
    {
        return array_key_exists('url', $this->data);
    }

    /**
     * @param non-empty-string $url URL, прикрепленный к сообщению в качестве предпросмотра медиа (minLength: 1).
     * @param non-empty-string|null $token Токен вложения (minLength: 1).
     */
    public static function make(string $url, ?string $token = null): self
    {
        return new self($url, $token);
    }

    /**
     * @param non-empty-string|null $url URL, прикрепленный к сообщению в качестве предпросмотра медиа (minLength: 1).
     * @param non-empty-string|null $token Токен вложения (minLength: 1).
     */
    public static function new(?string $url = null, ?string $token = null): self
    {
        return new self($url, $token);
    }

    /**
     * @param non-empty-string $token Токен вложения (minLength: 1).
     * @return $this
     */
    public function setToken(string $token): self
    {
        self::validateString('token', $token, minLength: 1);
        $this->data['token'] = $token;

        return $this;
    }

    /**
     * @param non-empty-string $url URL, прикрепленный к сообщению в качестве предпросмотра медиа (minLength: 1).
     * @return $this
     */
    public function setUrl(string $url): self
    {
        self::validateString('url', $url, minLength: 1);
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetToken(): self
    {
        unset($this->data['token']);

        return $this;
    }
}
