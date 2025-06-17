<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

use JsonException;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpExceptions\MaxHttpException;
use MaxMessenger\Bot\Models\Response\Error;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\JsonDecodeException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\UnexpectedContentTypeException;

/**
 * @implements HttpResponseInterface<JsonRequest>
 */
final class JsonResponse implements HttpResponseInterface
{
    protected bool $contentTypeValidated = false;

    public function __construct(
        public readonly JsonRequest $request,
        public readonly int $httpCode,
        public readonly string $url,
        public readonly string $effectiveUrl,
        public readonly ?string $redirectUrl,
        public readonly ?string $contentType,
        public readonly string $body
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws UnexpectedContentTypeException
     */
    public function checkContentType(string|array|null $expectedContentType = null): void
    {
        if (!$this->contentTypeValidated) {
            if ($this->contentType !== 'application/json; charset=utf-8') {
                /** @psalm-var HttpResponseInterface $this Psalm bug */
                throw new UnexpectedContentTypeException($this);
            }

            $this->contentTypeValidated = true;
        }
    }

    /**
     * @inheritDoc
     *
     * @throws MaxHttpException
     * @throws HttpException
     */
    public function checkHttpCode(int|array $allowedCode = 200): void
    {
        if ($this->getHttpCode() !== 200) {
            $error = Error::newFromData((array)$this->getData());

            /** @psalm-var HttpResponseInterface $this Psalm bug */
            $error->isValid()
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

    /**
     * @throws UnexpectedContentTypeException
     * @throws JsonDecodeException
     */
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
        return $this->effectiveUrl;
    }

    public function getFirstHeader(string $name): null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return [];
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): JsonRequest
    {
        return $this->request;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
