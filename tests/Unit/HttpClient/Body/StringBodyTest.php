<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Body;

use Codeception\Test\Unit;
use MaxMessenger\Bot\HttpClient\Body\StringBody;

final class StringBodyTest extends Unit
{
    public function testWithContentType(): void
    {
        $body = new StringBody('{"text":"привет"}', 'application/json; charset=utf-8');

        self::assertSame('{"text":"привет"}', $body->getBody());
        self::assertSame('application/json; charset=utf-8', $body->getContentType());
    }

    public function testWithoutContentType(): void
    {
        $body = new StringBody('plain text');

        self::assertSame('plain text', $body->getBody());
        self::assertNull($body->getContentType());
    }
}
