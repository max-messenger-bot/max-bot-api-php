<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exceptions\AccessTokenException;
use MaxMessenger\Bot\HttpClient\JsonRequest;
use MaxMessenger\Bot\HttpClient\JsonResponse;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;
use SensitiveParameterValue;

final class JsonRequestTest extends Unit
{
    private const TEST_TOKEN = 'Bearer test-token-123';
    private const TEST_URL = 'https://api.example.com/test';

    public function testGetBodyWithBody(): void
    {
        $body = (object)['name' => 'test'];
        $request = $this->createRequest(body: $body, method: HttpMethod::Post);

        $result = $request->getBody();

        self::assertSame('{"name":"test"}', $result->getBody());
        self::assertSame('application/json; charset=utf-8', $result->getContentType());
    }

    public function testGetBodyWithoutBody(): void
    {
        $request = $this->createRequest(method: HttpMethod::Get);

        $result = $request->getBody();

        self::assertSame('', $result->getBody());
    }

    public function testGetConnectTimeout(): void
    {
        $request = $this->createRequest();

        self::assertNull($request->getConnectTimeout());
    }

    public function testGetHeaders(): void
    {
        $request = $this->createRequest();

        $headers = $request->getHeaders();

        self::assertSame([
            'Accept: application/json; charset=utf-8',
            'Authorization: Bearer test-token-123',
        ], $headers);
    }

    public function testGetHeadersThrowsAccessTokenExceptionWhenEmpty(): void
    {
        /** @psalm-suppress InvalidArgument */
        $request = new JsonRequest(
            self::TEST_URL,
            null,
            HttpMethod::Get,
            null,
            null,
            new SensitiveParameterValue('')
        );

        try {
            $request->getHeaders();
            self::fail('Expected exception was not thrown');
        } catch (AccessTokenException $e) {
            self::assertStringContainsString('Access token', $e->getMessage());
        }
    }

    public function testGetMaxRedirects(): void
    {
        $request = $this->createRequest();

        self::assertNull($request->getMaxRedirects());
    }

    public function testGetMethod(): void
    {
        $request = $this->createRequest(method: HttpMethod::Post);

        self::assertSame('POST', $request->getMethod());
    }

    public function testGetMethodGet(): void
    {
        $request = $this->createRequest(method: HttpMethod::Get);

        self::assertSame('GET', $request->getMethod());
    }

    public function testGetTimeout(): void
    {
        $request = $this->createRequest(timeout: 30);

        self::assertSame(30, $request->getTimeout());
    }

    public function testGetTimeoutNull(): void
    {
        $request = $this->createRequest(timeout: null);

        self::assertNull($request->getTimeout());
    }

    public function testGetUrlWithQuery(): void
    {
        $request = $this->createRequest(query: ['foo' => 'bar', 'baz' => '123']);

        $url = $request->getUrl();

        self::assertStringContainsString(self::TEST_URL . '?', $url);
        self::assertStringContainsString('foo=bar', $url);
        self::assertStringContainsString('baz=123', $url);
    }

    public function testGetUrlWithoutQuery(): void
    {
        $request = $this->createRequest();

        self::assertSame(self::TEST_URL, $request->getUrl());
    }

    public function testIsFollowLocation(): void
    {
        $request = $this->createRequest();

        self::assertFalse($request->isFollowLocation());
    }

    public function testIsPostForDelete(): void
    {
        $request = $this->createRequest(method: HttpMethod::Delete);

        self::assertFalse($request->isPost());
    }

    public function testIsPostForGet(): void
    {
        $request = $this->createRequest(method: HttpMethod::Get);

        self::assertFalse($request->isPost());
    }

    public function testIsPostForPatch(): void
    {
        $request = $this->createRequest(method: HttpMethod::Patch);

        self::assertTrue($request->isPost());
    }

    public function testIsPostForPost(): void
    {
        $request = $this->createRequest(method: HttpMethod::Post);

        self::assertTrue($request->isPost());
    }

    public function testIsPostForPut(): void
    {
        $request = $this->createRequest(method: HttpMethod::Put);

        self::assertTrue($request->isPost());
    }

    public function testIsResponseHeadersRequired(): void
    {
        $request = $this->createRequest();

        self::assertFalse($request->isResponseHeadersRequired());
    }

    public function testMakeResponse(): void
    {
        $request = $this->createRequest(method: HttpMethod::Post);

        $response = $request->makeResponse(
            200,
            self::TEST_URL,
            self::TEST_URL,
            null,
            ['Content-Type' => ['application/json']],
            'application/json',
            '{"status":"ok"}'
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(200, $response->getHttpCode());
        self::assertSame('{"status":"ok"}', $response->getBody());
    }

    /**
     * @param non-empty-string $url
     * @param array<string|int, string|int> $query
     * @param positive-int|null $timeout
     * @param non-empty-string $token
     */
    private function createRequest(
        string $url = self::TEST_URL,
        ?array $query = null,
        HttpMethod $method = HttpMethod::Get,
        ?object $body = null,
        ?int $timeout = null,
        string $token = self::TEST_TOKEN
    ): JsonRequest {
        return new JsonRequest(
            $url,
            $query,
            $method,
            $body,
            $timeout,
            new SensitiveParameterValue($token)
        );
    }
}
