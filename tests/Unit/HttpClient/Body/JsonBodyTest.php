<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Body;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\HttpClient\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\HttpClient\Body\JsonBody;

final class JsonBodyTest extends Unit
{
    public function testGetBodySimple(): void
    {
        $data = [
            'name' => 'test',
            'value' => 123,
        ];

        $body = new JsonBody((object) $data);

        self::assertSame('{"name":"test","value":123}', $body->getBody());
    }

    public function testGetBodyThrowsJsonEncodeException(): void
    {
        // Ресурс не может быть закодирован в JSON
        $resource = fopen('php://memory', 'r');
        self::assertNotFalse($resource);

        $data = (object) ['resource' => $resource];

        $body = new JsonBody($data);

        try {
            $body->getBody();
            self::fail('Expected exception was not thrown');
        } catch (JsonEncodeException $e) {
            self::assertSame($data, $e->getData());
        } finally {
            fclose($resource);
        }
    }

    public function testGetBodyWithBooleanValues(): void
    {
        $data = [
            'active' => true,
            'disabled' => false,
        ];

        $body = new JsonBody((object) $data);

        self::assertSame('{"active":true,"disabled":false}', $body->getBody());
    }

    public function testGetBodyWithEmptyObject(): void
    {
        $data = [];

        $body = new JsonBody((object) $data);

        self::assertSame('{}', $body->getBody());
    }

    public function testGetBodyWithFloat(): void
    {
        $data = [
            'price' => 19.99,
            'discount' => 0.5,
        ];

        $body = new JsonBody((object) $data);

        self::assertSame('{"price":19.99,"discount":0.5}', $body->getBody());
    }

    public function testGetBodyWithNestedObjects(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ];

        $body = new JsonBody((object) $data);

        $expected = '{"user":{"name":"John","email":"john@example.com"}}';
        self::assertSame($expected, $body->getBody());
    }

    public function testGetBodyWithNullValues(): void
    {
        $data = [
            'name' => 'test',
            'value' => null,
        ];

        $body = new JsonBody((object) $data);

        self::assertSame('{"name":"test","value":null}', $body->getBody());
    }

    public function testGetBodyWithSpecialCharacters(): void
    {
        $data = [
            'text' => "line1\nline2\ttab\"quote\\slash",
        ];

        $body = new JsonBody((object) $data);

        self::assertSame('{"text":"line1\nline2\ttab\"quote\\\\slash"}', $body->getBody());
    }

    public function testGetBodyWithUnicode(): void
    {
        $data = [
            'text' => 'Привет, мир!',
            'emoji' => '🚀',
        ];

        $body = new JsonBody((object) $data);

        self::assertSame('{"text":"Привет, мир!","emoji":"🚀"}', $body->getBody());
    }

    public function testGetContentType(): void
    {
        $data = [];

        $body = new JsonBody((object) $data);

        self::assertSame('application/json; charset=utf-8', $body->getContentType());
    }
}
