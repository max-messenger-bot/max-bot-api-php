<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Body;

use JsonException;
use Mj4444\SimpleHttpClient\Contracts\HttpRequest\BodyInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpRequest\JsonEncodeException;

/**
 * JSON-тело для HTTP-запросов.
 */
final readonly class JsonBody implements BodyInterface
{
    public function __construct(
        private object $body,
    ) {
    }

    public function getBody(): string
    {
        try {
            return json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonEncodeException($this->body, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/json; charset=utf-8';
    }
}
