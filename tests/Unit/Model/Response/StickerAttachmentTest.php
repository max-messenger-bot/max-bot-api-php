<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\StickerAttachment;
use MaxMessenger\Bot\Model\Response\StickerAttachmentPayload;

final class StickerAttachmentTest extends Unit
{
    public function testGetters(): void
    {
        $attachment = StickerAttachment::newFromData([
            'type' => 'sticker',
            'payload' => ['url' => 'https://example.test/sticker.webp', 'code' => 'sticker-code'],
            'width' => 512,
            'height' => 256,
        ]);

        self::assertSame(AttachmentType::Sticker, $attachment->getType());
        self::assertSame(512, $attachment->getWidth());
        self::assertSame(256, $attachment->getHeight());

        $payload = $attachment->getPayload();
        self::assertInstanceOf(StickerAttachmentPayload::class, $payload);
        self::assertSame('sticker-code', $payload->getCode());
        self::assertSame($payload, $attachment->getPayload());
    }
}
