<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\ShareAttachmentPayload;
use MaxMessenger\Bot\Model\Request\ShareAttachmentRequest;

use function json_encode;

use const JSON_UNESCAPED_SLASHES;

final class ShareAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new ShareAttachmentPayload('https://example.test/');
        $attachment = new ShareAttachmentRequest($payload);

        self::assertSame(AttachmentType::Share, $attachment->getType());
        self::assertTrue($attachment->issetPayload());
        self::assertSame($payload, $attachment->getPayload());
        self::assertSame(
            '{"type":"share","payload":{"url":"https://example.test/"}}',
            json_encode($attachment, JSON_UNESCAPED_SLASHES),
        );
    }

    public function testMake(): void
    {
        $payload = new ShareAttachmentPayload('https://example.test/');

        self::assertSame($payload, ShareAttachmentRequest::make($payload)->getPayload());
    }

    public function testNewWithoutPayload(): void
    {
        $attachment = ShareAttachmentRequest::new();

        self::assertSame(AttachmentType::Share, $attachment->getType());
        self::assertFalse($attachment->issetPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testPayloadIsValidatedOnEncode(): void
    {
        $attachment = new ShareAttachmentRequest(new ShareAttachmentPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "url" field cannot be null.');

        self::assertNotFalse(json_encode($attachment));
    }

    public function testSetPayload(): void
    {
        $attachment = ShareAttachmentRequest::new();
        $payload = new ShareAttachmentPayload('https://example.test/');

        self::assertSame($attachment, $attachment->setPayload($payload));
        self::assertSame($payload, $attachment->getPayload());
        self::assertTrue($attachment->issetPayload());
    }
}
