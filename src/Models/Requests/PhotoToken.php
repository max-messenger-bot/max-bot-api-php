<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Exceptions\RequireArgumentException;

/**
 * @template-extends BaseRequestModel<array{
 *     token: non-empty-string
 * }>
 * @api
 */
class PhotoToken extends BaseRequestModel
{
    use ValidateTrait;

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
     * @api
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public static function new(?string $token = null): static
    {
        if ($token === null) {
            throw new RequireArgumentException('token');
        }

        return new static($token);
    }

    /**
     * @param non-empty-string $token Encoded information of uploaded image.
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
