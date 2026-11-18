<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\PhotoAttachment;
use MaxMessenger\Bot\Model\Response\PhotoAttachmentPayload;

final class PhotoAttachmentTest extends Unit
{
    public function testGetPayload(): void
    {
        $attachment = PhotoAttachment::newFromData([
            'type' => 'image',
            'payload' => [
                'photo_id' => 9876543210,
                'url' => 'https://example.test/photo.jpg',
                'token' => 'photo-token',
            ],
        ]);

        self::assertSame(AttachmentType::Image, $attachment->getType());

        $payload = $attachment->getPayload();
        self::assertInstanceOf(PhotoAttachmentPayload::class, $payload);
        self::assertSame(9876543210, $payload->getPhotoId());
        self::assertSame($payload, $attachment->getPayload());
    }
}
