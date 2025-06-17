<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

/**
 * Информация, которую вы получите, как только аудио/видео будет загружено.
 *
 * @api
 */
final class UploadedInfo extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     token: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     * @api
     */
    public function __construct(string $token)
    {
        $this->setToken($token);
    }

    /**
     * @return non-empty-string
     * @api
     */
    public function getToken(): string
    {
        return $this->data['token'];
    }

    /**
     * @param non-empty-string|null $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     * @psalm-param non-empty-string $token
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?string $token = null): static
    {
        static::validateNotNull('token', $token);

        return new static($token);
    }

    /**
     * @param non-empty-string $token Токен — уникальный ID загруженного медиафайла (minLength: 1).
     * @return $this
     * @api
     */
    public function setToken(string $token): static
    {
        static::validateString('token', $token, minLength: 1);

        $this->data['token'] = $token;

        return $this;
    }
}
