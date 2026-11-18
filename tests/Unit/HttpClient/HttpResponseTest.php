<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\HttpClient\HttpResponse;

final class HttpResponseTest extends Unit
{
    public function testDefaults(): void
    {
        $response = new HttpResponse(200);

        self::assertSame(200, $response->getHttpCode());
        self::assertNull($response->getContentType());
        self::assertSame('', $response->getBody());
        self::assertSame([], $response->getHeaders());
    }

    public function testGetFirstHeaderIgnoresCase(): void
    {
        $response = $this->createResponse(['retry-after' => ['30'], 'x-request-id' => ['abc', 'def']]);

        self::assertSame('30', $response->getFirstHeader('Retry-After'));
        self::assertSame('abc', $response->getFirstHeader('X-Request-Id'));
    }

    public function testGetFirstHeaderReturnsNullForUnknownHeader(): void
    {
        self::assertNull($this->createResponse([])->getFirstHeader('Retry-After'));
    }

    public function testGetters(): void
    {
        $headers = ['content-type' => ['application/json']];
        $response = new HttpResponse(404, 'application/json', '{"code":"x"}', $headers);

        self::assertSame(404, $response->getHttpCode());
        self::assertSame('application/json', $response->getContentType());
        self::assertSame('{"code":"x"}', $response->getBody());
        self::assertSame($headers, $response->getHeaders());
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private function createResponse(array $headers): HttpResponse
    {
        return new HttpResponse(200, 'application/json', '{}', $headers);
    }
}
