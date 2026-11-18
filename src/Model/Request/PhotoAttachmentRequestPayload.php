<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Response\PhotoToken;

use function array_key_exists;
use function sprintf;

/**
 * Данные для прикрепления изображения.
 *
 * Поля `url`, `token` и `photos` взаимоисключающие.
 *
 * Общее количество вложений не должно превышать 12.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 */
final class PhotoAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     url?: non-empty-string,
     *     token?: non-empty-string,
     *     photos?: object
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $url Любой внешний URL изображения, которое вы хотите прикрепить.
     * @param non-empty-string|null $token Токен существующего вложения.
     * @param non-empty-array<string, PhotoToken|array{token: non-empty-string}>|null $photos Токены,
     *     полученные после загрузки изображений.
     */
    public function __construct(?string $url = null, ?string $token = null, ?array $photos = null)
    {
        $this->requiredOnce = ['url', 'token', 'photos'];

        if ($url !== null) {
            $this->setUrl($url);
        } elseif ($token !== null) {
            $this->setToken($token);
        } elseif ($photos !== null) {
            $this->setPhotos($photos);
        }
    }

    /**
     * @return non-empty-array<string, PhotoToken|array{token: non-empty-string}>|null
     */
    public function getPhotos(): ?array
    {
        if (!isset($this->data['photos'])) {
            return null;
        }

        /** @var non-empty-array<string, PhotoToken|array{token: non-empty-string}> */
        return (array) $this->data['photos'];
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

    public function issetPhotos(): bool
    {
        return array_key_exists('photos', $this->data);
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
     * @param non-empty-string|null $url Любой внешний URL изображения, которое вы хотите прикрепить.
     * @param non-empty-string|null $token Токен существующего вложения.
     * @param non-empty-array<string, PhotoToken|array{token: non-empty-string}>|null $photos Токены,
     *     полученные после загрузки изображений.
     */
    public static function make(?string $url = null, ?string $token = null, ?array $photos = null): self
    {
        return new self($url, $token, $photos);
    }

    /**
     * @param non-empty-string|null $url Любой внешний URL изображения, которое вы хотите прикрепить.
     * @param non-empty-string|null $token Токен существующего вложения.
     * @param non-empty-array<string, PhotoToken|array{token: non-empty-string}>|null $photos Токены,
     *     полученные после загрузки изображений.
     */
    public static function new(?string $url = null, ?string $token = null, ?array $photos = null): self
    {
        return new self($url, $token, $photos);
    }

    /**
     * @param non-empty-array<string, PhotoToken|array{token: non-empty-string}> $photos Токены,
     *     полученные после загрузки изображений.
     */
    public static function newWithPhotos(array $photos): self
    {
        return new self(photos: $photos);
    }

    /**
     * @param non-empty-string $token Токен существующего вложения.
     */
    public static function newWithToken(string $token): self
    {
        return new self(token: $token);
    }

    /**
     * @param non-empty-string $url Любой внешний URL изображения, которое вы хотите прикрепить.
     */
    public static function newWithUrl(string $url): self
    {
        return new self(url: $url);
    }

    /**
     * @param non-empty-array<string, PhotoToken|array{token: non-empty-string}> $photos Токены,
     *     полученные после загрузки изображений.
     * @return $this
     */
    public function setPhotos(array $photos): self
    {
        self::validateArray('photos', $photos, minItems: 1);

        foreach ($photos as $id => $photo) {
            self::validateString(
                sprintf('photos[%s].token', $id),
                $photo instanceof PhotoToken ? $photo->getToken() : $photo['token'],
                minLength: 1,
            );
        }

        $this->data['photos'] = (object) $photos;
        unset($this->data['url'], $this->data['token']);

        return $this;
    }

    /**
     * @param non-empty-string $token Токен существующего вложения.
     * @return $this
     */
    public function setToken(string $token): self
    {
        self::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;
        unset($this->data['url'], $this->data['photos']);

        return $this;
    }

    /**
     * @param non-empty-string $url Любой внешний URL изображения, которое вы хотите прикрепить.
     * @return $this
     */
    public function setUrl(string $url): self
    {
        self::validateString('url', $url, minLength: 1);

        $this->data['url'] = $url;
        unset($this->data['token'], $this->data['photos']);

        return $this;
    }
}
