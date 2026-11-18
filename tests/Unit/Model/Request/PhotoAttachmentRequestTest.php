<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\PhotoAttachmentRequest;
use MaxMessenger\Bot\Model\Request\PhotoAttachmentRequestPayload;

use function json_encode;

final class PhotoAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = PhotoAttachmentRequestPayload::newWithToken('photo-token');
        $attachment = new PhotoAttachmentRequest($payload);

        self::assertSame(AttachmentType::Image, $attachment->getType());
        self::assertTrue($attachment->issetPayload());
        self::assertSame($payload, $attachment->getPayload());
        self::assertSame('{"type":"image","payload":{"token":"photo-token"}}', json_encode($attachment));
    }

    public function testMake(): void
    {
        $payload = PhotoAttachmentRequestPayload::newWithToken('photo-token');

        self::assertSame($payload, PhotoAttachmentRequest::make($payload)->getPayload());
    }

    public function testNewWithoutPayload(): void
    {
        $attachment = PhotoAttachmentRequest::new();

        self::assertSame(AttachmentType::Image, $attachment->getType());
        self::assertFalse($attachment->issetPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testPayloadIsValidatedOnEncode(): void
    {
        $attachment = new PhotoAttachmentRequest(new PhotoAttachmentRequestPayload());

        $this->expectException(RequiredOneFieldException::class);

        self::assertNotFalse(json_encode($attachment));
    }

    public function testSetPayload(): void
    {
        $attachment = PhotoAttachmentRequest::new();
        $payload = PhotoAttachmentRequestPayload::newWithToken('photo-token');

        self::assertSame($attachment, $attachment->setPayload($payload));
        self::assertSame($payload, $attachment->getPayload());
        self::assertTrue($attachment->issetPayload());
    }
}
