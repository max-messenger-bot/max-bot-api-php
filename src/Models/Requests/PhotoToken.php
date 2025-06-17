<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

/**
 * @api
 */
final class PhotoToken extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     token: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param non-empty-string $token Encoded information of uploaded image.
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
     * @param non-empty-string|null $token Encoded information of uploaded image.
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
     * @param non-empty-string $token Encoded information of uploaded image (minLength: 1).
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
