<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\HttpClient\Body\StringBody;
use MaxMessenger\Bot\HttpClient\HttpRequest;

final class HttpRequestTest extends Unit
{
    public function testDefaults(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/messages');

        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.test/messages', $request->getUrl());
        self::assertSame([], $request->getHeaders());
        self::assertNull($request->getBody());
        self::assertNull($request->getTimeout());
    }

    public function testWithBodyAndHeaders(): void
    {
        $body = new StringBody('{"text":"привет"}', 'application/json; charset=utf-8');
        $request = new HttpRequest(
            'POST',
            'https://example.test/messages?chat_id=1',
            ['Authorization' => 'token'],
            $body,
            30_000,
        );

        self::assertSame('POST', $request->getMethod());
        self::assertSame(['Authorization' => 'token'], $request->getHeaders());
        self::assertSame($body, $request->getBody());
        self::assertSame(30_000, $request->getTimeout());
    }
}
