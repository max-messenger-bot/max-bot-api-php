<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Upload;

use JsonException;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http\MaxHttpException;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\UploadException;
use MaxMessenger\Bot\Models\Responses\Error;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\JsonDecodeException;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\UnexpectedContentTypeException;

use function is_array;

/**
 * HTTP-ответ для простой загрузки файла.
 *
 * @implements HttpResponseInterface<SimpleUploadRequest>
 */
final class SimpleUploadResponse implements HttpResponseInterface
{
    protected bool $contentTypeValidated = false;

    public function __construct(
        public readonly SimpleUploadRequest $request,
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
     */
    public function checkContentType(string|array|null $expectedContentType = null): void
    {
        if (!$this->contentTypeValidated) {
            if (!str_starts_with($this->contentType ?? '', 'application/json')) {
                /** @psalm-var HttpResponseInterface $this Psalm bug */
                throw new UnexpectedContentTypeException($this);
            }

            $this->contentTypeValidated = true;
        }
    }

    /**
     * @inheritDoc
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

    public function getData(): mixed
    {
        $this->checkContentType();

        try {
            /** @psalm-suppress MixedAssignment */
            $data = json_decode($this->body, true, 4, JSON_THROW_ON_ERROR);

            if (is_array($data) && isset($data['error_code'], $data['error_data'])) {
                $error = Error::newFromData(['code' => $data['error_code'], 'message' => $data['error_data']]);
                if ($error->isValid()) {
                    /** @psalm-var HttpResponseInterface $this Psalm bug */
                    throw new UploadException($this, $error);
                }
            }

            return $data;
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
    public function getRequest(): SimpleUploadRequest
    {
        return $this->request;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
