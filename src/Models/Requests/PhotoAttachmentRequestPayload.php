<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

/**
 * @template-extends BaseRequestModel<array{
 *     url?: non-empty-string|null,
 *     token?: non-empty-string|null,
 *     photos?: object|null
 * }>
 * @api
 */
class PhotoAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @param non-empty-string|null $url Any external image URL you want to attach.
     * @param non-empty-string|null $token Token of any existing attachment.
     * @param array<non-empty-string, PhotoToken>|null $photos Tokens were obtained after uploading images.
     * @api
     */
    public function __construct(?string $url = null, ?string $token = null, ?array $photos = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        }
        if ($token !== null) {
            $this->setToken($token);
        }
        if ($photos !== null) {
            $this->setPhotos($photos);
        }
    }

    /**
     * @return array<non-empty-string, PhotoToken>|null
     * @api
     */
    public function getPhotos(): ?array
    {
        $photos = $this->data['photos'] ?? null;

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
        return isset($this->data['photos']);
    }

    /**
     * @api
     */
    public function issetToken(): bool
    {
        return isset($this->data['token']);
    }

    /**
     * @api
     */
    public function issetUrl(): bool
    {
        return isset($this->data['url']);
    }

    /**
     * @param array<non-empty-string, PhotoToken>|null $photos Tokens were obtained after uploading images.
     * @return $this
     * @api
     */
    public function setPhotos(?array $photos = null): static
    {
        $this->data['photos'] = (object)$photos;

        return $this;
    }

    /**
     * @param non-empty-string|null $token Token of any existing attachment.
     * @return $this
     * @api
     */
    public function setToken(?string $token = null): static
    {
        static::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;

        return $this;
    }

    /**
     * @param non-empty-string|null $url Any external image URL you want to attach.
     * @return $this
     * @api
     */
    public function setUrl(?string $url = null): static
    {
        static::validateString('url', $url, minLength: 1);

        $this->data['url'] = $url;

        return $this;
    }

    /**
     * @api
     */
    public function unsetPhotos(): static
    {
        unset($this->data['photos']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetToken(): static
    {
        unset($this->data['token']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetUrl(): static
    {
        unset($this->data['url']);

        return $this;
    }
}
