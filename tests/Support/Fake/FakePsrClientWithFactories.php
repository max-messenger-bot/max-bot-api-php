<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Фейковый клиент PSR-18, который сам является фабриками PSR-17, как это делает Guzzle.
 */
final class FakePsrClientWithFactories implements ClientInterface, RequestFactoryInterface, StreamFactoryInterface
{
    public ?RequestInterface $lastRequest = null;
    private readonly Psr17Factory $factory;

    public function __construct(private readonly ?ResponseInterface $response = null)
    {
        $this->factory = new Psr17Factory();
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->factory->createRequest($method, $uri);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return $this->factory->createStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->factory->createStreamFromFile($filename, $mode);
    }

    /**
     * @param resource $resource Ресурс с содержимым потока.
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->factory->createStreamFromResource($resource);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;

        return $this->response ?? throw new LogicException('Response is not configured.');
    }
}
