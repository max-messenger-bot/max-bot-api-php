<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\CurlHttpClient\Exception\CurlException;
use MaxMessenger\Bot\Exception\AccessTokenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ServiceUnavailableException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\JsonDecodeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\UnexpectedContentTypeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\UnexpectedFormatException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\HttpResponse;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use SensitiveParameterValue;

use function count;

use const JSON_ERROR_UTF8;

final class MaxHttpClientTest extends Unit
{
    private const BASE_URL = 'https://api.example.com';
    private const TEST_TOKEN = 'Bearer test-token-123';

    public function testAcceptsJsonContentTypeWithCharset(): void
    {
        $mockHttpClient = $this->createMockHttpClient(
            static fn(): HttpResponse => new HttpResponse(200, 'application/json; charset=utf-8', '{"ok":true}'),
        );
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        self::assertSame(['ok' => true], $client->get('/test/get'));
    }

    public function testAccessTokenEmptyThrowsException(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->expects(self::never())->method('request');
        $config = $this->createMock(MaxApiConfigInterface::class);
        $config->method('getAccessToken')->willReturn(new SensitiveParameterValue(''));
        $config->method('getHttpClient')->willReturn($mockHttpClient);

        $client = new MaxHttpClient($config);

        $this->expectException(AccessTokenException::class);

        $client->get('/test/get');
    }

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
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringStartsWith(self::BASE_URL, $url);
            self::assertStringContainsString('/test/base', $url);

            return $this->createResponse(200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/base');

        self::assertSame(['ok' => true], $result);
    }

    public function testConstructorClientsTakePrecedenceOverConfig(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $updatesClient = $this->createMock(HttpClientInterface::class);
        $uploaderClient = $this->createMock(HttpClientInterface::class);
        $config = $this->createMock(MaxApiConfigInterface::class);
        $config->expects(self::never())->method('getHttpClient');
        $config->expects(self::never())->method('getHttpClientForUpdates');
        $config->expects(self::never())->method('getHttpClientForUploader');

        $client = new MaxHttpClient(
            $config,
            httpClient: $httpClient,
            httpClientForUpdates: $updatesClient,
            httpClientForUploader: $uploaderClient,
        );

        self::assertSame($httpClient, $client->getHttpClient());
        self::assertSame($updatesClient, $client->getHttpClientForUpdates());
        self::assertSame($uploaderClient, $client->getHttpClientForUploader());
    }

    public function testDelete(): void
    {
        $capturedRequest = null;
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) use (&$capturedRequest) {
            $capturedRequest = $request;

            return $this->createResponse(200, '{"result":"deleted"}');
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
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('foo=bar', $url);

            return $this->createResponse(200, '{"result":"ok"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->delete('/test/delete', ['foo' => 'bar']);

        self::assertSame(['result' => 'ok'], $result);
    }

    public function testDoRequestReturnsEmptyArrayForEmptyObjectResponse(): void
    {
        $mockHttpClient = $this->createMockHttpClient(fn(): HttpResponse => $this->createResponse(200, '{}'));
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get');

        self::assertSame([], $result);
    }

    public function testDoRequestReturnsValidResponseWithNestedEmptyArray(): void
    {
        $mockHttpClient = $this->createMockHttpClient(
            fn(): HttpResponse => $this->createResponse(200, '{"data":[]}'),
        );
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get');

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertSame([], $result['data']);
    }

    public function testDoRequestThrowsBadRequestExceptionWithoutRetry(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function () use (&$attempts): HttpResponse {
            ++$attempts;

            return $this->createResponse(400, '{"code":"chat.not.found","message":"Chat not found"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1, 1]);

        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Expected exception was not thrown');
        } catch (BadRequestException $e) {
            self::assertSame('chat.not.found', $e->getError()?->getCode());
            self::assertSame('Chat not found', $e->getMessage());
            self::assertSame(1, $attempts);
        }
    }

    public function testDoRequestThrowsHttpExceptionWithoutErrorForUnparsableBody(): void
    {
        $mockHttpClient = $this->createMockHttpClient(
            static fn(): HttpResponse => new HttpResponse(404, 'text/html', 'Not Found'),
        );
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Expected exception was not thrown');
        } catch (NotFoundException $e) {
            self::assertNull($e->getError());
            self::assertSame('HTTP error 404', $e->getMessage());
            self::assertSame('Not Found', $e->getResponse()->getBody());
        }
    }

    public function testDoRequestThrowsJsonDecodeExceptionForInvalidJson(): void
    {
        $mockHttpClient = $this->createMockHttpClient(fn(): HttpResponse => $this->createResponse(200, '{"ok":'));
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        $this->expectException(JsonDecodeException::class);

        $client->get('/test/get');
    }

    public function testDoRequestThrowsUnexpectedContentTypeException(): void
    {
        $mockHttpClient = $this->createMockHttpClient(
            static fn(): HttpResponse => new HttpResponse(200, 'text/html', '<html></html>'),
        );
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        $this->expectException(UnexpectedContentTypeException::class);

        $client->get('/test/get');
    }

    public function testDoRequestThrowsUnexpectedFormatExceptionForNonArrayResponse(): void
    {
        $mockHttpClient = $this->createMockHttpClient(fn(): HttpResponse => $this->createResponse(200, '"string"'));
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Expected exception was not thrown');
        } catch (UnexpectedFormatException $e) {
            self::assertStringContainsString('Unexpected Response Format', $e->getMessage());
        }
    }

    public function testDoesNotLogWhenRetryAttemptsRunOut(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function () use (&$attempts): HttpResponse {
            ++$attempts;

            return $this->createResponse(503, '{"code":"service.unavailable","message":"Try later"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1]);
        $logged = 0;
        $logger = static function () use (&$logged): void {
            ++$logged;
        };

        $client = new MaxHttpClient($config, exceptionLogger: $logger);

        try {
            $client->get('/test/get');
            self::fail('Ожидалась ошибка HTTP 503');
        } catch (ServiceUnavailableException) {
            self::assertSame(2, $attempts);
            self::assertSame(1, $logged);
        }
    }

    public function testDoesNotRetryPermanentCurlError(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(static function (HttpRequest $request) use (&$attempts): never {
            ++$attempts;

            throw new CurlException('Unsupported protocol', CURLE_UNSUPPORTED_PROTOCOL, $request);
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1, 1]);
        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Ожидалась ошибка curl');
        } catch (CurlException $e) {
            self::assertSame(1, $attempts);
        }
    }

    public function testGet(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            self::assertSame('GET', $request->getMethod());
            self::assertStringContainsString('/test/get', $request->getUrl());

            return $this->createResponse(200, '{"data":"value"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get');

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertSame('value', $result['data']);
    }

    public function testGetHttpClientForUploaderReturnsConfiguredClient(): void
    {
        $uploaderClient = $this->createMock(HttpClientInterface::class);
        $config = $this->createMockConfig();
        $config->method('getHttpClientForUploader')->willReturn($uploaderClient);

        $client = new MaxHttpClient($config);

        self::assertSame($uploaderClient, $client->getHttpClientForUploader());
    }

    public function testGetHttpClientIsTakenFromConfigOnce(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $config = $this->createMock(MaxApiConfigInterface::class);
        $config->expects(self::once())->method('getHttpClient')->willReturn($httpClient);
        $config->expects(self::once())->method('getHttpClientForUpdates')->willReturn(null);

        $client = new MaxHttpClient($config);

        self::assertSame($httpClient, $client->getHttpClient());
        self::assertSame($httpClient, $client->getHttpClient());
        self::assertSame($httpClient, $client->getHttpClientForUpdates());
        self::assertSame($httpClient, $client->getHttpClientForUpdates());
    }

    public function testGetUpdatesRetriesWithUpdatesClient(): void
    {
        $attempts = 0;
        $updatesClient = $this->createMockHttpClient(function (HttpRequest $request) use (&$attempts): HttpResponse {
            if (++$attempts === 1) {
                throw new TemporaryNetworkException(new RuntimeException('Connection reset by peer'), $request);
            }

            return $this->createResponse(200, '{"updates":[]}');
        });
        $defaultClient = $this->createMock(HttpClientInterface::class);
        $defaultClient->expects(self::never())->method('request');
        $config = $this->createMockConfig(mockHttpClient: $defaultClient, retryAttempts: [1]);
        $config->method('getHttpClientForUpdates')->willReturn($updatesClient);

        $client = new MaxHttpClient($config);

        self::assertSame(['updates' => []], $client->getUpdates('/updates', ['timeout' => 30], 40000));
        self::assertSame(2, $attempts);
    }

    public function testGetUpdatesWithTimeout(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            self::assertSame(30, $request->getTimeout());

            return $this->createResponse(200, '{"timeout":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->getUpdates('/updates', null, 30);

        self::assertSame(['timeout' => true], $result);
    }

    public function testGetWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('id=123', $url);
            self::assertStringContainsString('type=test', $url);

            return $this->createResponse(200, '{"item":{"id":123}}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->get('/test/get', ['id' => 123, 'type' => 'test']);

        self::assertIsArray($result);
        self::assertArrayHasKey('item', $result);
    }

    public function testJsonEncodeErrorIsThrownBeforeRequest(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->expects(self::never())->method('request');
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);
        $body = (object) ['text' => "\xB1\x31"];

        $client = new MaxHttpClient($config);

        try {
            $client->post('/test/post', $body);
            self::fail('Ожидалась ошибка кодирования JSON');
        } catch (JsonEncodeException $e) {
            self::assertSame($body, $e->getData());
            self::assertInstanceOf(JsonException::class, $e->getPrevious());
            self::assertSame(JSON_ERROR_UTF8, $e->getCode());
            self::assertSame('Malformed UTF-8 characters, possibly incorrectly encoded', $e->getMessage());
        }
    }

    public function testMakeRequestSetsAcceptAndAuthorizationHeaders(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request): HttpResponse {
            $headers = $request->getHeaders();
            self::assertSame('application/json; charset=utf-8', $headers['Accept'] ?? null);
            self::assertSame(self::TEST_TOKEN, $headers['Authorization'] ?? null);

            return $this->createResponse(200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);

        self::assertSame(['ok' => true], $client->get('/test/get'));
    }

    public function testPatch(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            self::assertSame('PATCH', $request->getMethod());
            $body = $request->getBody();
            self::assertNotNull($body);
            self::assertSame('{"name":"updated"}', $body->getBody());

            return $this->createResponse(200, '{"status":"patched"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->patch('/test/patch', (object) ['name' => 'updated']);

        self::assertIsArray($result);
        self::assertSame('patched', $result['status']);
    }

    public function testPatchWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('param=value', $url);

            return $this->createResponse(200, '{"status":"ok"}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->patch('/test/patch', (object) ['foo' => 'bar'], ['param' => 'value']);

        self::assertSame(['status' => 'ok'], $result);
    }

    public function testPost(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            self::assertSame('POST', $request->getMethod());
            $body = $request->getBody();
            self::assertNotNull($body);
            self::assertSame('{"message":"hello"}', $body->getBody());

            return $this->createResponse(200, '{"id":42}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->post('/test/post', (object) ['message' => 'hello']);

        self::assertIsArray($result);
        self::assertArrayHasKey('id', $result);
        self::assertSame(42, $result['id']);
    }

    public function testPostRetryAttemptsCanBeDisabled(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) use (&$attempts) {
            ++$attempts;

            throw new TemporaryNetworkException(new RuntimeException('Connection reset by peer'), $request);
        });
        $config = $this->createMockConfig(
            mockHttpClient: $mockHttpClient,
            retryAttempts: [1],
            postRetryAttempts: [],
        );

        $client = new MaxHttpClient($config);

        try {
            $client->post('/test/post', (object) ['text' => 'привет']);
            self::fail('Ожидалась ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertSame('Connection reset by peer', $e->getMessage());
            self::assertSame(1, $attempts);
        }
    }

    public function testPostUsesOwnRetryAttempts(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) use (&$attempts) {
            ++$attempts;
            if ($attempts === 1) {
                throw new TemporaryNetworkException(new RuntimeException('Connection reset by peer'), $request);
            }

            return $this->createResponse(200, '{"sent":true}');
        });
        $config = $this->createMockConfig(
            mockHttpClient: $mockHttpClient,
            retryAttempts: [],
            postRetryAttempts: [1],
        );

        $client = new MaxHttpClient($config);

        self::assertSame(['sent' => true], $client->post('/test/post', (object) ['text' => 'привет']));
        self::assertSame(2, $attempts);
    }

    public function testPostWithNullBody(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            self::assertSame('POST', $request->getMethod());

            return $this->createResponse(200, '{"created":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->post('/test/post', null);

        self::assertSame(['created' => true], $result);
    }

    public function testPostWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('chat_id=123', $url);

            return $this->createResponse(200, '{"sent":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->post('/test/post', (object) ['text' => 'hi'], ['chat_id' => 123]);

        self::assertSame(['sent' => true], $result);
    }

    public function testPut(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            self::assertSame('PUT', $request->getMethod());
            $body = $request->getBody();
            self::assertNotNull($body);
            self::assertSame('{"title":"new title"}', $body->getBody());

            return $this->createResponse(200, '{"updated":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->put('/test/put', (object) ['title' => 'new title']);

        self::assertIsArray($result);
        self::assertTrue($result['updated']);
    }

    public function testPutUsesCommonRetryAttempts(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) use (&$attempts): HttpResponse {
            if (++$attempts === 1) {
                throw new TemporaryNetworkException(new RuntimeException('Connection reset by peer'), $request);
            }

            return $this->createResponse(200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1], postRetryAttempts: []);

        $client = new MaxHttpClient($config);

        self::assertSame(['ok' => true], $client->put('/test/put', (object) ['data' => 'new']));
        self::assertSame(2, $attempts);
    }

    public function testPutWithQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('force=true', $url);

            return $this->createResponse(200, '{"replaced":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config);
        $result = $client->put('/test/put', (object) ['data' => 'new'], ['force' => 'true']);

        self::assertSame(['replaced' => true], $result);
    }

    public function testRetriesTemporaryExceptionAndResendsBody(): void
    {
        $bodies = [];
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) use (&$bodies) {
            $bodies[] = $request->getBody()?->getBody();

            if (count($bodies) === 1) {
                throw new TemporaryNetworkException(new RuntimeException('Connection reset by peer'), $request);
            }

            return $this->createResponse(200, '{"sent":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1]);

        $client = new MaxHttpClient($config);
        $result = $client->post('/test/post', (object) ['text' => 'привет']);

        self::assertSame(['sent' => true], $result);
        // При postRetryAttempts = null применяется общее расписание; тело сохраняется.
        self::assertSame(['{"text":"привет"}', '{"text":"привет"}'], $bodies);
    }

    public function testRetriesTemporaryHttpStatusAndLogsException(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function () use (&$attempts): HttpResponse {
            ++$attempts;

            return $attempts === 1
                ? $this->createResponse(503, '{"code":"service.unavailable","message":"Try later"}')
                : $this->createResponse(200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1]);
        $logged = [];
        $logger = static function (string $method, TemporaryExceptionInterface $exception) use (&$logged): void {
            $logged[] = [$method, $exception::class];
        };

        $client = new MaxHttpClient($config, exceptionLogger: $logger);

        self::assertSame(['ok' => true], $client->get('/test/get'));
        self::assertSame(2, $attempts);
        self::assertSame([[MaxHttpClient::class . '::doRequest', ServiceUnavailableException::class]], $logged);
    }

    public function testSetExceptionLogger(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function () use (&$attempts): HttpResponse {
            ++$attempts;

            return $attempts % 2 === 1
                ? $this->createResponse(503, '{"code":"service.unavailable","message":"Try later"}')
                : $this->createResponse(200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1]);
        $logged = [];
        $logger = static function (string $method, TemporaryExceptionInterface $exception) use (&$logged): void {
            $logged[] = $exception::class;
        };

        $client = new MaxHttpClient($config);

        self::assertSame($client, $client->setExceptionLogger($logger));
        self::assertSame(['ok' => true], $client->get('/test/get'));
        self::assertSame([ServiceUnavailableException::class], $logged);

        $client->setExceptionLogger(null);

        self::assertSame(['ok' => true], $client->get('/test/get'));
        self::assertSame(4, $attempts);
        self::assertSame([ServiceUnavailableException::class], $logged);
    }

    public function testSetVersion(): void
    {
        $config = $this->createMockConfig();

        $client = new MaxHttpClient($config);
        $result = $client->setVersion('0.0.10');

        self::assertSame($client, $result);
        self::assertSame('0.0.10', $client->version);
    }

    public function testSetVersionNull(): void
    {
        $config = $this->createMockConfig();

        $client = new MaxHttpClient($config, '0.0.10');
        $client->setVersion(null);

        self::assertNull($client->version);
    }

    public function testThrowsTemporaryExceptionWhenRetryAttemptsRunOut(): void
    {
        $attempts = 0;
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) use (&$attempts) {
            ++$attempts;

            throw new TemporaryNetworkException(new RuntimeException('Connection reset by peer'), $request);
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient, retryAttempts: [1, 1]);

        $client = new MaxHttpClient($config);

        try {
            $client->get('/test/get');
            self::fail('Ожидалась ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertSame('Connection reset by peer', $e->getMessage());
            self::assertSame(3, $attempts);
        }
    }

    public function testVersionIncludedInQueryForGet(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('v=0.0.10', $url);

            return $this->createResponse(200, '{"versioned":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = (new MaxHttpClient($config))->setVersion('0.0.10');
        $result = $client->get('/test/versioned');

        self::assertSame(['versioned' => true], $result);
    }

    public function testVersionIncludedInQueryForGetWithExistingQuery(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('foo=bar', $url);
            self::assertStringContainsString('v=0.0.10', $url);

            return $this->createResponse(200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config, '0.0.10');
        $result = $client->get('/test/versioned', ['foo' => 'bar']);

        self::assertSame(['ok' => true], $result);
    }

    public function testVersionNotOverwrittenIfAlreadySet(): void
    {
        $mockHttpClient = $this->createMockHttpClient(function (HttpRequest $request) {
            $url = $request->getUrl();
            self::assertStringContainsString('v=1.0.0', $url);
            self::assertStringNotContainsString('v=0.0.10', $url);

            return $this->createResponse(200, '{"ok":true}');
        });
        $config = $this->createMockConfig(mockHttpClient: $mockHttpClient);

        $client = new MaxHttpClient($config, '0.0.10');
        $result = $client->get('/test/versioned', ['v' => '1.0.0']);

        self::assertSame(['ok' => true], $result);
    }

    /**
     * @param non-empty-string|null $accessToken
     * @param list<positive-int> $retryAttempts
     * @param list<positive-int>|null $postRetryAttempts
     * @return MaxApiConfigInterface&MockObject
     */
    private function createMockConfig(
        ?string $accessToken = self::TEST_TOKEN,
        ?HttpClientInterface $mockHttpClient = null,
        array $retryAttempts = [],
        ?array $postRetryAttempts = null,
    ): MaxApiConfigInterface {
        $mock = $this->createMock(MaxApiConfigInterface::class);
        $mock->method('getAccessToken')->willReturn(
            $accessToken !== null ? new SensitiveParameterValue($accessToken) : null,
        );
        $mock->method('getBaseUrl')->willReturn(self::BASE_URL);
        $mock->method('getHttpClient')->willReturn($mockHttpClient ?? $this->createMock(HttpClientInterface::class));
        $mock->method('getPostRetryAttempts')->willReturn($postRetryAttempts ?? $retryAttempts);
        $mock->method('getRetryAttempts')->willReturn($retryAttempts);

        return $mock;
    }

    /**
     * @param callable(HttpRequest): HttpResponse $handler
     */
    private function createMockHttpClient(callable $handler): HttpClientInterface
    {
        $mock = $this->createMock(HttpClientInterface::class);
        $mock
            ->method('request')
            ->willReturnCallback(function (HttpRequestInterface $request) use ($handler) {
                self::assertInstanceOf(HttpRequest::class, $request);

                return $handler($request);
            });

        return $mock;
    }

    private function createResponse(int $httpCode, string $body): HttpResponse
    {
        return new HttpResponse($httpCode, 'application/json', $body);
    }
}
