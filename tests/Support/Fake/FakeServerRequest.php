<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

use function implode;
use function strtolower;

/**
 * Минимальная реализация {@see ServerRequestInterface} для герметичных тестов: хранит метод,
 * заголовки и тело запроса, остальные методы интерфейса не поддерживаются.
 */
final class FakeServerRequest implements ServerRequestInterface
{
    /**
     * @var array<string, list<string>>
     */
    private array $headers = [];

    /**
     * @param array<string, string> $headers Заголовки запроса.
     */
    public function __construct(
        private readonly string $method = 'POST',
        private readonly string $body = '',
        array $headers = [],
    ) {
        foreach ($headers as $name => $value) {
            $this->headers[strtolower($name)] = [$value];
        }
    }

    public function getAttribute(string $name, $default = null): mixed
    {
        return $default;
    }

    public function getAttributes(): array
    {
        return [];
    }

    public function getBody(): StreamInterface
    {
        return new FakeStream($this->body);
    }

    public function getCookieParams(): array
    {
        return [];
    }

    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParsedBody(): null
    {
        return null;
    }

    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function getQueryParams(): array
    {
        return [];
    }

    public function getRequestTarget(): string
    {
        return '/';
    }

    public function getServerParams(): array
    {
        return [];
    }

    public function getUploadedFiles(): array
    {
        return [];
    }

    public function getUri(): UriInterface
    {
        throw new LogicException('Not implemented.');
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function withAddedHeader(string $name, $value): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withAttribute(string $name, $value): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withBody(StreamInterface $body): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withCookieParams(array $cookies): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withHeader(string $name, $value): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withMethod(string $method): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withParsedBody($data): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withProtocolVersion(string $version): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withQueryParams(array $query): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withRequestTarget(string $requestTarget): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withUploadedFiles(array $uploadedFiles): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withoutAttribute(string $name): never
    {
        throw new LogicException('Not implemented.');
    }

    public function withoutHeader(string $name): never
    {
        throw new LogicException('Not implemented.');
    }
}
