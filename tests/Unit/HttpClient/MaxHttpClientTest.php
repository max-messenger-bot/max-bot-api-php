<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Exception\AccessTokenException;
use MaxMessenger\Bot\Exception\HttpClient\HttpResponse\UnexpectedFormatException;
use MaxMessenger\Bot\HttpClient\JsonRequest;
use MaxMessenger\Bot\HttpClient\JsonResponse;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use Mj4444\SimpleHttpClient\Contracts\HttpRequestInterface;
use SensitiveParameterValue;

final class MaxHttpClientTest extends Unit
{
    private const TEST_TOKEN = 'Bearer test-token-123';
    private const BASE_URL = 'https://api.example.com';

    public function testAccessTokenNullThrowsException(): void
    {
        $config = $this->createMockConfig(accessToken: null);

        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Expected exception was not thrown');
        } catch (AccessTokenException $e) {
            self::assertStringContainsString('Access token', $e->getMessage());
        }
    }

    public function testBaseUrlUsedInRequest(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringStartsWith(self::BASE_URL, $url);
            self::assertStringContainsString('/test/base', $url);
            return $this->createJsonResponse($request, 200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/base');

        self::assertSame(['ok' => true], $result);
    }

    public function testDelete(): void
    {
        $capturedRequest = null;
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) use (&$capturedRequest) {
            $capturedRequest = $request;
            return $this->createJsonResponse($request, 200, '{"result":"deleted"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->delete('/test/delete');

        self::assertNotNull($capturedRequest);
        self::assertSame('DELETE', $capturedRequest->getMethod());
        self::assertStringContainsString('/test/delete', $capturedRequest->getUrl());
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertSame('deleted', $result['result']);
    }

    public function testDeleteWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('foo=bar', $url);
            return $this->createJsonResponse($request, 200, '{"result":"ok"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->delete('/test/delete', ['foo' => 'bar']);

        self::assertSame(['result' => 'ok'], $result);
    }

    public function testDoRequestReturnsValidResponseWithNestedEmptyArray(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            return $this->createJsonResponse($request, 200, '{"data":[]}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get');

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertSame([], $result['data']);
    }

    public function testDoRequestThrowsUnexpectedFormatExceptionForEmptyResponse(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            return $this->createJsonResponse($request, 200, '{}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Expected exception was not thrown');
        } catch (UnexpectedFormatException $e) {
            self::assertStringContainsString('Unexpected Response Format', $e->getMessage());
        }
    }

    public function testDoRequestThrowsUnexpectedFormatExceptionForNonArrayResponse(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            return $this->createJsonResponse($request, 200, '"string"');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Expected exception was not thrown');
        } catch (UnexpectedFormatException $e) {
            self::assertStringContainsString('Unexpected Response Format', $e->getMessage());
        }
    }

    public function testGet(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            self::assertSame('GET', $request->getMethod());
            self::assertStringContainsString('/test/get', $request->getUrl());
            return $this->createJsonResponse($request, 200, '{"data":"value"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get');

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertSame('value', $result['data']);
    }

    public function testGetWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('id=123', $url);
            self::assertStringContainsString('type=test', $url);
            return $this->createJsonResponse($request, 200, '{"item":{"id":123}}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get', ['id' => 123, 'type' => 'test']);

        self::assertIsArray($result);
        self::assertArrayHasKey('item', $result);
    }

    public function testGetWithTimeout(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            self::assertSame(30, $request->getTimeout());
            return $this->createJsonResponse($request, 200, '{"timeout":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get', null, 30);

        self::assertSame(['timeout' => true], $result);
    }

    public function testPatch(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            self::assertSame('PATCH', $request->getMethod());
            self::assertSame('{"name":"updated"}', $request->getBody()->getBody());
            return $this->createJsonResponse($request, 200, '{"status":"patched"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->patch('/test/patch', (object)['name' => 'updated']);

        self::assertIsArray($result);
        self::assertSame('patched', $result['status']);
    }

    public function testPatchWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('param=value', $url);
            return $this->createJsonResponse($request, 200, '{"status":"ok"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->patch('/test/patch', (object)['foo' => 'bar'], ['param' => 'value']);

        self::assertSame(['status' => 'ok'], $result);
    }

    public function testPost(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            self::assertSame('POST', $request->getMethod());
            self::assertSame('{"message":"hello"}', $request->getBody()->getBody());
            return $this->createJsonResponse($request, 200, '{"id":42}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->post('/test/post', (object)['message' => 'hello']);

        self::assertIsArray($result);
        self::assertArrayHasKey('id', $result);
        self::assertSame(42, $result['id']);
    }

    public function testPostWithNullBody(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            self::assertSame('POST', $request->getMethod());
            return $this->createJsonResponse($request, 200, '{"created":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->post('/test/post', null);

        self::assertSame(['created' => true], $result);
    }

    public function testPostWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('chat_id=123', $url);
            return $this->createJsonResponse($request, 200, '{"sent":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->post('/test/post', (object)['text' => 'hi'], ['chat_id' => 123]);

        self::assertSame(['sent' => true], $result);
    }

    public function testPut(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            self::assertSame('PUT', $request->getMethod());
            self::assertSame('{"title":"new title"}', $request->getBody()->getBody());
            return $this->createJsonResponse($request, 200, '{"updated":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->put('/test/put', (object)['title' => 'new title']);

        self::assertIsArray($result);
        self::assertTrue($result['updated']);
    }

    public function testPutWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('force=true', $url);
            return $this->createJsonResponse($request, 200, '{"replaced":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->put('/test/put', (object)['data' => 'new'], ['force' => 'true']);

        self::assertSame(['replaced' => true], $result);
    }

    public function testVersionIncludedInQueryForGet(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('v=0.0.10', $url);
            return $this->createJsonResponse($request, 200, '{"versioned":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config, '0.0.10');
        $result = $client->get('/test/versioned');

        self::assertSame(['versioned' => true], $result);
    }

    public function testVersionIncludedInQueryForGetWithExistingQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('foo=bar', $url);
            self::assertStringContainsString('v=0.0.10', $url);
            return $this->createJsonResponse($request, 200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config, '0.0.10');
        $result = $client->get('/test/versioned', ['foo' => 'bar']);

        self::assertSame(['ok' => true], $result);
    }

    public function testVersionNotOverwrittenIfAlreadySet(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (JsonRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('v=1.0.0', $url);
            self::assertStringNotContainsString('v=0.0.10', $url);
            return $this->createJsonResponse($request, 200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config, '0.0.10');
        $result = $client->get('/test/versioned', ['v' => '1.0.0']);

        self::assertSame(['ok' => true], $result);
    }

    public function testWithVersion(): void
    {
        $config = $this->createMockConfig();

        $client = new MaxHttpClient($config);
        $clientWithVersion = $client->withVersion('0.0.10');

        self::assertNotSame($client, $clientWithVersion);
        self::assertNull($client->version);
        self::assertSame('0.0.10', $clientWithVersion->version);
    }

    public function testWithVersionNull(): void
    {
        $config = $this->createMockConfig();

        $client = new MaxHttpClient($config, '0.0.10');
        $clientWithoutVersion = $client->withVersion(null);

        self::assertNull($clientWithoutVersion->version);
    }

    private function createJsonResponse(JsonRequest $request, int $httpCode, string $body): JsonResponse
    {
        return new JsonResponse(
            $request,
            $httpCode,
            $request->getUrl(),
            'application/json',
            $body
        );
    }

    /**
     * @param non-empty-string|null $accessToken
     */
    private function createMockConfig(
        ?string $accessToken = self::TEST_TOKEN,
        ?HttpClientInterface $mockHttpClient = null
    ): MaxApiConfigInterface {
        $mock = $this->createMock(MaxApiConfigInterface::class);
        $mock->method('getAccessToken')->willReturn(
            $accessToken !== null ? new SensitiveParameterValue($accessToken) : null
        );
        $mock->method('getBaseUrl')->willReturn(self::BASE_URL);
        $mock->method('getHttpClient')->willReturn($mockHttpClient ?? $this->createMock(HttpClientInterface::class));

        return $mock;
    }

    /**
     * @param callable(JsonRequest): JsonResponse $handler
     */
    private function createMockHttpClient(callable $handler): HttpClientInterface
    {
        $mock = $this->createMock(HttpClientInterface::class);
        $mock->method('request')
            ->willReturnCallback(function (HttpRequestInterface $request) use ($handler) {
                self::assertInstanceOf(JsonRequest::class, $request);
                return $handler($request);
            });

        return $mock;
    }
}
