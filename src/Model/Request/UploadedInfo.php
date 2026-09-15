<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\MaxApiClient;

use function array_key_exists;

/**
 * Данные, которые вы получили в ответ на запрос загрузки медиафайла.
 *
 * Их можно передавать после того, как вы загрузили аудио, видео или файл и получили в ответ от сервера `retval`.
 */
final class UploadedInfo extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     token: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $token Токен вложения — уникальный ID загруженного медиа:
     *     изображения, аудио, видео или файла. Возвращается в ответ на вызов {@see MaxApiClient::getUploadUrl()}.
     */
    public function __construct(?string $token = null)
    {
        $this->required = ['token'];

        if ($token !== null) {
            $this->setToken($token);
        }
    }

    /**
     * @return non-empty-string
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    public function issetToken(): bool
    {
        return array_key_exists('token', $this->data);
    }

    /**
     * @param non-empty-string $token Токен вложения — уникальный ID загруженного медиа:
     *     изображения, аудио, видео или файла. Возвращается в ответ на вызов {@see MaxApiClient::getUploadUrl()}.
     */
    public static function make(string $token): self
    {
        return new self($token);
    }

    /**
     * @param non-empty-string|null $token Токен вложения — уникальный ID загруженного медиа:
     *     изображения, аудио, видео или файла. Возвращается в ответ на вызов {@see MaxApiClient::getUploadUrl()}.
     */
    public static function new(?string $token = null): self
    {
        return new self($token);
    }

    /**
     * @param non-empty-string $token Токен вложения — уникальный ID загруженного медиа:
     *     изображения, аудио, видео или файла. Возвращается в ответ на вызов {@see MaxApiClient::getUploadUrl()}.
     * @return $this
     */
    public function setToken(string $token): self
    {
        self::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;

        return $this;
    }
}
