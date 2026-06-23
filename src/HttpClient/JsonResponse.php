<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use JsonException;
use MaxMessenger\Bot\Exception\HttpClient\HttpResponse\JsonDecodeException;
use MaxMessenger\Bot\Exception\HttpClient\HttpResponse\UnexpectedContentTypeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\MaxHttpException;
use MaxMessenger\Bot\Model\Response\Error;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;
use Throwable;

use function json_decode;
use function str_starts_with;

/**
 * JSON HTTP-ответ.
 *
 * @implements HttpResponseInterface<JsonRequest>
 * @internal
 */
final readonly class JsonResponse implements HttpResponseInterface
{
    public function __construct(
        public JsonRequest $request,
        public int $httpCode,
        public string $url,
        public ?string $contentType,
        public string $body,
    ) {}

    /**
     * Параметр `$expectedContentType` определён интерфейсом {@see HttpResponseInterface}, но в этой реализации
     * не используется: ожидается только `application/json`.
     */
    public function checkContentType(string|array|null $expectedContentType = null): void
    {
        if (!str_starts_with($this->contentType ?? '', 'application/json')) {
            /** @psalm-var HttpResponseInterface $this Psalm bug */
            throw new UnexpectedContentTypeException($this);
        }
    }

    /**
     * Параметр `$allowedCode` определён интерфейсом {@see HttpResponseInterface}, но в этой реализации
     * не используется: API Max считает успешным только код 200.
     */
    public function checkHttpCode(int|array $allowedCode = 200): void
    {
        if ($this->getHttpCode() !== 200) {
            try {
                $error = Error::newFromData((array) $this->getData());
            } catch (Throwable) {
                $error = null;
            }

            /** @psalm-var HttpResponseInterface $this Psalm bug */
            $error !== null && $error->isValid()
                ? MaxHttpException::throwMax($this, $error)
                : HttpException::throw($this, [200]);
        }
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getData(): mixed
    {
        $this->checkContentType();

        try {
            return json_decode($this->body, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            /** @psalm-var HttpResponseInterface $this Psalm bug */
            throw new JsonDecodeException($this, $e);
        }
    }

    public function getEffectiveUrl(): string
    {
        return $this->url;
    }

    public function getFirstHeader(string $name): null
    {
        return null;
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getRedirectUrl(): null
    {
        return null;
    }

    public function getRequest(): JsonRequest
    {
        return $this->request;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
