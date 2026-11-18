<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\FileAttachmentRequest;
use MaxMessenger\Bot\Model\Request\UploadedInfo;

use function json_encode;

final class FileAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new UploadedInfo('file-token');
        $attachment = new FileAttachmentRequest($payload);

        self::assertSame(AttachmentType::File, $attachment->getType());
        self::assertTrue($attachment->issetPayload());
        self::assertSame($payload, $attachment->getPayload());
        self::assertSame('{"type":"file","payload":{"token":"file-token"}}', json_encode($attachment));
    }

    public function testMake(): void
    {
        $payload = new UploadedInfo('file-token');

        self::assertSame($payload, FileAttachmentRequest::make($payload)->getPayload());
    }

    public function testNewWithoutPayload(): void
    {
        $attachment = FileAttachmentRequest::new();

        self::assertSame(AttachmentType::File, $attachment->getType());
        self::assertFalse($attachment->issetPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testPayloadIsValidatedOnEncode(): void
    {
        $attachment = new FileAttachmentRequest(new UploadedInfo());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "token" field cannot be null.');

        self::assertNotFalse(json_encode($attachment));
    }

    public function testSetPayload(): void
    {
        $attachment = FileAttachmentRequest::new();
        $payload = new UploadedInfo('file-token');

        self::assertSame($attachment, $attachment->setPayload($payload));
        self::assertSame($payload, $attachment->getPayload());
        self::assertTrue($attachment->issetPayload());
    }
}
