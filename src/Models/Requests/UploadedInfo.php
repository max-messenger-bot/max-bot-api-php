<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Информация, которую вы получите, как только аудио/видео будет загружено.
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
     * @param non-empty-string|null $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
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
     * @param non-empty-string $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     */
    public static function make(string $token): self
    {
        return new self($token);
    }

    /**
     * @param non-empty-string|null $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     */
    public static function new(?string $token = null): self
    {
        return new self($token);
    }

    /**
     * @param non-empty-string $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     * @return $this
     */
    public function setToken(string $token): self
    {
        self::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;

        return $this;
    }
}
