<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\RequireArgumentsException;

use function array_key_exists;

/**
 * Request to attach image.
 *
 * All fields are mutually exclusive.
 *
 * @api
 */
final class PhotoAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     photos?: object|null,
     *     token?: non-empty-string|null,
     *     url?: non-empty-string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param non-empty-string|null $url Any external image URL you want to attach (minLength: 1).
     * @param non-empty-string|null $token Token of any existing attachment (minLength: 1).
     * @param non-empty-array<PhotoToken>|null $photos Tokens were obtained after uploading images (minItems: 1).
     * @api
     */
    public function __construct(?string $url = null, ?string $token = null, ?array $photos = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        } elseif ($token !== null) {
            $this->setToken($token);
        } elseif ($photos !== null) {
            $this->setPhotos($photos);
        } else {
            throw new RequireArgumentsException();
        }
    }

    /**
     * @return array<PhotoToken>|null
     * @api
     */
    public function getPhotos(): ?array
    {
        $photos = $this->data['photos'] ?? null;

        /** @psalm-var array<PhotoToken>|null */
        return $photos !== null ? (array)$photos : null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getToken(): ?string
    {
        return $this->data['token'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }

    /**
     * @api
     */
    public function issetPhotos(): bool
    {
        return array_key_exists('photos', $this->data);
    }

    /**
     * @api
     */
    public function issetToken(): bool
    {
        return array_key_exists('token', $this->data);
    }

    /**
     * @api
     */
    public function issetUrl(): bool
    {
        return array_key_exists('url', $this->data);
    }

    /**
     * @param non-empty-array<PhotoToken> $photos Tokens were obtained after uploading images (minItems: 1).
     * @api
     */
    public static function newWithPhotos(array $photos): static
    {
        return new static(photos: $photos);
    }

    /**
     * @param non-empty-string $token Token of any existing attachment (minLength: 1).
     * @api
     */
    public static function newWithToken(string $token): static
    {
        return new static(token: $token);
    }

    /**
     * @param non-empty-string $url Any external image URL you want to attach (minLength: 1).
     * @api
     */
    public static function newWithUrl(string $url): static
    {
        return new static(url: $url);
    }

    /**
     * @param non-empty-array<PhotoToken>|null $photos Tokens were obtained after uploading images (minItems: 1).
     * @return $this
     * @api
     */
    public function setPhotos(?array $photos = null): static
    {
        static::validateArray('photos', $photos, minItems: 1);

        $this->data['photos'] = (object)$photos;
        unset($this->data['url'], $this->data['token']);

        return $this;
    }

    /**
     * @param non-empty-string|null $token Token of any existing attachment (minLength: 1).
     * @return $this
     * @api
     */
    public function setToken(?string $token = null): static
    {
        static::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;
        unset($this->data['url'], $this->data['photos']);

        return $this;
    }

    /**
     * @param non-empty-string|null $url Any external image URL you want to attach (minLength: 1).
     * @return $this
     * @api
     */
    public function setUrl(?string $url = null): static
    {
        static::validateString('url', $url, minLength: 1);

        $this->data['url'] = $url;
        unset($this->data['token'], $this->data['photos']);

        return $this;
    }
}
