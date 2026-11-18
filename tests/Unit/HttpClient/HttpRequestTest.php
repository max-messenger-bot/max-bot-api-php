<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\HttpClient\Body\StringBody;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use SensitiveParameterValue;

use function print_r;

final class HttpRequestTest extends Unit
{
    public function testAccessToken(): void
    {
        $request = new HttpRequest(
            'GET',
            'https://example.test/messages',
            ['Accept' => 'application/json'],
            new SensitiveParameterValue('secret-access-token'),
        );

        self::assertSame(
            ['Accept' => 'application/json', 'Authorization' => 'secret-access-token'],
            $request->getHeaders(),
        );
        self::assertStringNotContainsString('secret-access-token', print_r($request, true));
    }

    public function testDefaults(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/messages');

        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.test/messages', $request->getUrl());
        self::assertSame([], $request->getHeaders());
        self::assertNull($request->getBody());
        self::assertNull($request->getTimeout());
        self::assertNull($request->getLowSpeedLimit());
        self::assertNull($request->getLowSpeedTime());
    }

    public function testTimeLimits(): void
    {
        $request = new HttpRequest(
            'PUT',
            'https://example.test/upload',
            timeout: 30000,
            lowSpeedLimit: 1024,
            lowSpeedTime: 15,
        );

        self::assertInstanceOf(HttpRequestInterface::class, $request);
        self::assertSame(30000, $request->getTimeout());
        self::assertSame(1024, $request->getLowSpeedLimit());
        self::assertSame(15, $request->getLowSpeedTime());
    }

    public function testWithBodyAndHeaders(): void
    {
        $body = new StringBody('{"text":"привет"}', 'application/json; charset=utf-8');
        $request = new HttpRequest(
            'POST',
            'https://example.test/messages?chat_id=1',
            ['Authorization' => 'token'],
            null,
            $body,
            30_000,
        );

        self::assertSame('POST', $request->getMethod());
        self::assertSame(['Authorization' => 'token'], $request->getHeaders());
        self::assertSame($body, $request->getBody());
        self::assertSame(30_000, $request->getTimeout());
    }
}
