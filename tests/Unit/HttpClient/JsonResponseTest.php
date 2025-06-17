<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse\JsonDecodeException;
use MaxMessenger\Bot\Exceptions\HttpClient\HttpResponse\UnexpectedContentTypeException;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http\MaxHttpException;
use MaxMessenger\Bot\HttpClient\JsonRequest;
use MaxMessenger\Bot\HttpClient\JsonResponse;
use MaxMessenger\Bot\Models\Responses\Error;
use Mj4444\SimpleHttpClient\Exceptions\HttpResponse\Http\HttpException;
use Mj4444\SimpleHttpClient\HttpRequest\HttpMethod;
use SensitiveParameterValue;

final class JsonResponseTest extends Unit
{
    private const TEST_TOKEN = 'Bearer test-token-123';
    private const TEST_URL = 'https://api.example.com/test';

    public function testCheckContentTypeInvalidThrowsException(): void
    {
        $response = $this->createResponse(contentType: 'text/html');

        try {
            $response->checkContentType();
            self::fail('Expected exception was not thrown');
        } catch (UnexpectedContentTypeException $e) {
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testCheckContentTypeNullThrowsException(): void
    {
        $response = $this->createResponse(contentType: null);

        try {
            $response->checkContentType();
            self::fail('Expected exception was not thrown');
        } catch (UnexpectedContentTypeException $e) {
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testCheckContentTypeValid(): void
    {
        $response = $this->createResponse();

        $response->checkContentType();
        self::assertTrue(true);
    }

    public function testCheckContentTypeWithCharset(): void
    {
        $response = $this->createResponse(contentType: 'application/json; charset=utf-8');

        $response->checkContentType();
        self::assertTrue(true);

        $response = $this->createResponse(contentType: 'application/json;charset=utf-8');

        $response->checkContentType();
        self::assertTrue(true);
    }

    public function testCheckContentTypeWithExpectedContentType(): void
    {
        $response = $this->createResponse();

        $response->checkContentType('application/json');
        self::assertTrue(true);
    }

    public function testCheckHttpCodeErrorWithInvalidErrorModel(): void
    {
        $errorBody = json_encode([
            'foo' => 'bar',
        ]);
        self::assertNotFalse($errorBody);
        $response = $this->createResponse(httpCode: 400, body: $errorBody);

        try {
            $response->checkHttpCode();
            self::fail('Expected exception was not thrown');
        } catch (HttpException $e) {
            self::assertNotInstanceOf(MaxHttpException::class, $e);
        }
    }

    public function testCheckHttpCodeErrorWithValidErrorModel(): void
    {
        $errorBody = json_encode([
            'code' => 'bad.request',
            'message' => 'Bad Request',
        ]);
        self::assertNotFalse($errorBody);
        $response = $this->createResponse(httpCode: 400, body: $errorBody);

        try {
            $response->checkHttpCode();
            self::fail('Expected exception was not thrown');
        } catch (MaxHttpException $e) {
            self::assertSame(400, $e->getCode());
            self::assertSame($response, $e->getResponse());
            self::assertInstanceOf(Error::class, $e->getError());
            self::assertSame('bad.request', $e->getError()->getCode());
            self::assertSame('Bad Request', $e->getError()->getMessage());
        }
    }

    public function testCheckHttpCodeSuccess(): void
    {
        $response = $this->createResponse();

        $response->checkHttpCode();
        self::assertTrue(true);
    }

    public function testCheckHttpCodeWithAllowedCode(): void
    {
        $response = $this->createResponse();

        $response->checkHttpCode();
        self::assertTrue(true);

        $response->checkHttpCode(300);
        self::assertTrue(true);

        $response->checkHttpCode([300]);
        self::assertTrue(true);
    }

    public function testConstructor(): void
    {
        $request = $this->createRequest();
        $response = new JsonResponse(
            $request,
            200,
            self::TEST_URL,
            'application/json',
            '{"status":"ok"}'
        );

        self::assertSame($request, $response->request);
        self::assertSame(200, $response->httpCode);
        self::assertSame(self::TEST_URL, $response->url);
        self::assertSame('application/json', $response->contentType);
        self::assertSame('{"status":"ok"}', $response->body);
    }

    public function testGetBody(): void
    {
        $body = '{"test":"value"}';
        $response = $this->createResponse(body: $body);

        self::assertSame($body, $response->getBody());
    }

    public function testGetContentType(): void
    {
        $response = $this->createResponse();

        self::assertSame('application/json', $response->getContentType());
    }

    public function testGetContentTypeNull(): void
    {
        $response = $this->createResponse(contentType: null);

        self::assertNull($response->getContentType());
    }

    public function testGetDataInvalidJsonThrowsException(): void
    {
        $body = 'not valid json';
        $response = $this->createResponse(body: $body);

        try {
            $response->getData();
            self::fail('Expected exception was not thrown');
        } catch (JsonDecodeException $e) {
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testGetDataValid(): void
    {
        $body = '{"key":"value","number":42}';
        $response = $this->createResponse(body: $body);

        $data = $response->getData();

        self::assertIsArray($data);
        self::assertArrayHasKey('key', $data);
        self::assertSame('value', $data['key']);
        self::assertSame(42, $data['number']);
    }

    public function testGetDataWithNestedStructure(): void
    {
        $body = json_encode([
            'user' => [
                'name' => 'John',
                'age' => 30,
                'active' => true,
            ],
            'items' => [1, 2, 3],
        ]);
        self::assertNotFalse($body);
        $response = $this->createResponse(body: $body);

        $data = $response->getData();

        self::assertIsArray($data);
        self::assertSame('John', $data['user']['name'] ?? null);
        self::assertSame(30, $data['user']['age'] ?? null);
        self::assertTrue($data['user']['active'] ?? null);
        self::assertSame([1, 2, 3], $data['items'] ?? null);
    }

    public function testGetEffectiveUrl(): void
    {
        $response = $this->createResponse();

        self::assertSame(self::TEST_URL, $response->getEffectiveUrl());
    }

    public function testGetFirstHeader(): void
    {
        $response = $this->createResponse();

        self::assertNull($response->getFirstHeader('Content-Type'));
    }

    public function testGetHeaders(): void
    {
        $response = $this->createResponse();

        self::assertSame([], $response->getHeaders());
    }

    public function testGetHttpCode(): void
    {
        $response = $this->createResponse(httpCode: 404);

        self::assertSame(404, $response->getHttpCode());
    }

    public function testGetRedirectUrl(): void
    {
        $response = $this->createResponse();

        self::assertNull($response->getRedirectUrl());
    }

    public function testGetRequest(): void
    {
        $request = $this->createRequest();
        $response = $this->createResponse(request: $request);

        self::assertSame($request, $response->getRequest());
    }

    public function testGetUrl(): void
    {
        $response = $this->createResponse();

        self::assertSame(self::TEST_URL, $response->getUrl());
    }

    private function createRequest(): JsonRequest
    {
        return new JsonRequest(
            self::TEST_URL,
            null,
            HttpMethod::Get,
            null,
            null,
            new SensitiveParameterValue(self::TEST_TOKEN)
        );
    }

    /**
     * @param non-empty-string $url
     */
    private function createResponse(
        ?JsonRequest $request = null,
        int $httpCode = 200,
        string $url = self::TEST_URL,
        ?string $contentType = 'application/json',
        string $body = '{}'
    ): JsonResponse {
        return new JsonResponse(
            $request ?? $this->createRequest(),
            $httpCode,
            $url,
            $contentType,
            $body
        );
    }
}
