<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\StickerAttachmentRequest;
use MaxMessenger\Bot\Model\Request\StickerAttachmentRequestPayload;

use function json_encode;

final class StickerAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new StickerAttachmentRequestPayload('sticker-code');
        $attachment = new StickerAttachmentRequest($payload);

        self::assertSame(AttachmentType::Sticker, $attachment->getType());
        self::assertTrue($attachment->issetPayload());
        self::assertSame($payload, $attachment->getPayload());
        self::assertSame('{"type":"sticker","payload":{"code":"sticker-code"}}', json_encode($attachment));
    }

    public function testMake(): void
    {
        $payload = new StickerAttachmentRequestPayload('sticker-code');

        self::assertSame($payload, StickerAttachmentRequest::make($payload)->getPayload());
    }

    public function testNewWithoutPayload(): void
    {
        $attachment = StickerAttachmentRequest::new();

        self::assertSame(AttachmentType::Sticker, $attachment->getType());
        self::assertFalse($attachment->issetPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testPayloadIsValidatedOnEncode(): void
    {
        $attachment = new StickerAttachmentRequest(new StickerAttachmentRequestPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "code" field cannot be null.');

        self::assertNotFalse(json_encode($attachment));
    }

    public function testSetPayload(): void
    {
        $attachment = StickerAttachmentRequest::new();
        $payload = new StickerAttachmentRequestPayload('sticker-code');

        self::assertSame($attachment, $attachment->setPayload($payload));
        self::assertSame($payload, $attachment->getPayload());
        self::assertTrue($attachment->issetPayload());
    }
}
