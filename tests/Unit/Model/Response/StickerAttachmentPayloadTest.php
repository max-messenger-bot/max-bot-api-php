<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\AttachmentPayload;
use MaxMessenger\Bot\Model\Response\StickerAttachmentPayload;

final class StickerAttachmentPayloadTest extends Unit
{
    public function testGetters(): void
    {
        $payload = StickerAttachmentPayload::newFromData([
            'url' => 'https://example.test/sticker.webp',
            'code' => 'sticker-code',
        ]);

        self::assertInstanceOf(AttachmentPayload::class, $payload);
        self::assertSame('https://example.test/sticker.webp', $payload->getUrl());
        self::assertSame('sticker-code', $payload->getCode());
    }
}
