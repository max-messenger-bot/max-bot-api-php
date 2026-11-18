<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\UploadEndpoint;

final class UploadEndpointTest extends Unit
{
    public function testGetters(): void
    {
        $endpoint = UploadEndpoint::newFromData([
            'url' => 'https://upload.example.test/files?sig=abc',
            'token' => 'video-token',
        ]);

        self::assertSame('https://upload.example.test/files?sig=abc', $endpoint->getUrl());
        self::assertSame('video-token', $endpoint->getToken());
    }

    public function testWithoutToken(): void
    {
        self::assertNull(UploadEndpoint::newFromData(['url' => 'https://upload.example.test/files'])->getToken());
    }
}
