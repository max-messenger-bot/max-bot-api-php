<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\AttachmentPayload;
use MaxMessenger\Bot\Model\Response\MediaAttachmentPayload;

final class MediaAttachmentPayloadTest extends Unit
{
    public function testGetters(): void
    {
        $payload = MediaAttachmentPayload::newFromData([
            'url' => 'https://example.test/video.mp4',
            'id' => 1234567890,
            'token' => 'media-token',
        ]);

        self::assertInstanceOf(AttachmentPayload::class, $payload);
        self::assertSame('https://example.test/video.mp4', $payload->getUrl());
        self::assertSame(1234567890, $payload->getId());
        self::assertSame('media-token', $payload->getToken());
    }
}
