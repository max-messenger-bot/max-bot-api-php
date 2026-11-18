<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use function array_key_exists;

/**
 * Данные загруженного медиафайла.
 *
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/media
 * @link https://dev.max.ru/docs-api/methods/POST/uploads/
 */
final class UploadedInfo extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     token: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $token Токен загруженного аудио, видео или файла.
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
     * @param non-empty-string $token Токен загруженного аудио, видео или файла.
     */
    public static function make(string $token): self
    {
        return new self($token);
    }

    /**
     * @param non-empty-string|null $token Токен загруженного аудио, видео или файла.
     */
    public static function new(?string $token = null): self
    {
        return new self($token);
    }

    /**
     * @param non-empty-string $token Токен загруженного аудио, видео или файла.
     * @return $this
     */
    public function setToken(string $token): self
    {
        self::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;

        return $this;
    }
}
