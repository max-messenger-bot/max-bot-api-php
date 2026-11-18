<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\ShareAttachment;
use MaxMessenger\Bot\Model\Response\ShareAttachmentPayload;

final class ShareAttachmentTest extends Unit
{
    public function testGetters(): void
    {
        $attachment = ShareAttachment::newFromData([
            'type' => 'share',
            'payload' => ['url' => 'https://example.test/article', 'token' => 'share-token'],
            'title' => 'Статья',
            'description' => 'Краткое описание статьи',
            'image_url' => 'https://example.test/preview.jpg',
        ]);

        self::assertSame(AttachmentType::Share, $attachment->getType());
        self::assertSame('Статья', $attachment->getTitle());
        self::assertSame('Краткое описание статьи', $attachment->getDescription());
        self::assertSame('https://example.test/preview.jpg', $attachment->getImageUrl());

        $payload = $attachment->getPayload();
        self::assertInstanceOf(ShareAttachmentPayload::class, $payload);
        self::assertSame('https://example.test/article', $payload->getUrl());
        self::assertSame($payload, $attachment->getPayload());
    }

    public function testWithoutOptionalFields(): void
    {
        $attachment = ShareAttachment::newFromData([
            'type' => 'share',
            'payload' => ['url' => 'https://example.test/article'],
        ]);

        self::assertNull($attachment->getTitle());
        self::assertNull($attachment->getDescription());
        self::assertNull($attachment->getImageUrl());
    }
}
