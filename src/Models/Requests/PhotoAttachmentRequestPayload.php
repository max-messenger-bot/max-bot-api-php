<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Запрос на прикрепление изображения.
 *
 * Все поля являются взаимоисключающими.
 */
final class PhotoAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     url?: non-empty-string,
     *     token?: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $url Любой внешний URL изображения, которое вы хотите прикрепить (minLength: 1).
     * @param non-empty-string|null $token Токен существующего вложения (minLength: 1).
     */
    public function __construct(?string $url = null, ?string $token = null)
    {
        $this->requiredOnce = ['url', 'token'];

        if ($url !== null) {
            $this->setUrl($url);
        } elseif ($token !== null) {
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
     * @return non-empty-string|null
     */
    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
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
     * @param non-empty-string|null $url Любой внешний URL изображения, которое вы хотите прикрепить (minLength: 1).
     * @param non-empty-string|null $token Токен существующего вложения (minLength: 1).
     */
    public static function make(?string $url = null, ?string $token = null): self
    {
        return new self($url, $token);
    }

    /**
     * @param non-empty-string|null $url Любой внешний URL изображения, которое вы хотите прикрепить (minLength: 1).
     * @param non-empty-string|null $token Токен существующего вложения (minLength: 1).
     */
    public static function new(?string $url = null, ?string $token = null): self
    {
        return new self($url, $token);
    }

    /**
     * @param non-empty-string $token Токен существующего вложения (minLength: 1).
     */
    public static function newWithToken(string $token): self
    {
        return new self(token: $token);
    }

    /**
     * @param non-empty-string $url Любой внешний URL изображения, которое вы хотите прикрепить (minLength: 1).
     */
    public static function newWithUrl(string $url): self
    {
        return new self(url: $url);
    }

    /**
     * @param non-empty-string $token Токен существующего вложения (minLength: 1).
     * @return $this
     */
    public function setToken(string $token): self
    {
        self::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;
        unset($this->data['url']);

        return $this;
    }

    /**
     * @param non-empty-string $url Любой внешний URL изображения, которое вы хотите прикрепить (minLength: 1).
     * @return $this
     */
    public function setUrl(string $url): self
    {
        self::validateString('url', $url, minLength: 1);

        $this->data['url'] = $url;
        unset($this->data['token']);

        return $this;
    }
}
