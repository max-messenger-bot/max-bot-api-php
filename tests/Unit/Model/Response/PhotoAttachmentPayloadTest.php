<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\PhotoAttachmentPayload;

final class PhotoAttachmentPayloadTest extends Unit
{
    public function testGetters(): void
    {
        $payload = PhotoAttachmentPayload::newFromData([
            'photo_id' => 9876543210,
            'url' => 'https://example.test/photo.jpg',
            'token' => 'photo-token',
        ]);

        self::assertSame(9876543210, $payload->getPhotoId());
        self::assertSame('https://example.test/photo.jpg', $payload->getUrl());
        self::assertSame('photo-token', $payload->getToken());
    }
}
