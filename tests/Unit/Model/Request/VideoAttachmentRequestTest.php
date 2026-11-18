<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\UploadedInfo;
use MaxMessenger\Bot\Model\Request\VideoAttachmentRequest;

use function json_encode;

final class VideoAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new UploadedInfo('video-token');
        $attachment = new VideoAttachmentRequest($payload);

        self::assertSame(AttachmentType::Video, $attachment->getType());
        self::assertTrue($attachment->issetPayload());
        self::assertSame($payload, $attachment->getPayload());
        self::assertSame('{"type":"video","payload":{"token":"video-token"}}', json_encode($attachment));
    }

    public function testMake(): void
    {
        $payload = new UploadedInfo('video-token');

        self::assertSame($payload, VideoAttachmentRequest::make($payload)->getPayload());
    }

    public function testNewWithoutPayload(): void
    {
        $attachment = VideoAttachmentRequest::new();

        self::assertSame(AttachmentType::Video, $attachment->getType());
        self::assertFalse($attachment->issetPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testPayloadIsValidatedOnEncode(): void
    {
        $attachment = new VideoAttachmentRequest(new UploadedInfo());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "token" field cannot be null.');

        self::assertNotFalse(json_encode($attachment));
    }

    public function testSetPayload(): void
    {
        $attachment = VideoAttachmentRequest::new();
        $payload = new UploadedInfo('video-token');

        self::assertSame($attachment, $attachment->setPayload($payload));
        self::assertSame($payload, $attachment->getPayload());
        self::assertTrue($attachment->issetPayload());
    }
}
