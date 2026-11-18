<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\AudioAttachmentRequest;
use MaxMessenger\Bot\Model\Request\UploadedInfo;

use function json_encode;

final class AudioAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new UploadedInfo('audio-token');
        $attachment = new AudioAttachmentRequest($payload);

        self::assertSame(AttachmentType::Audio, $attachment->getType());
        self::assertTrue($attachment->issetPayload());
        self::assertSame($payload, $attachment->getPayload());
        self::assertSame('{"type":"audio","payload":{"token":"audio-token"}}', json_encode($attachment));
    }

    public function testMake(): void
    {
        $payload = new UploadedInfo('audio-token');

        self::assertSame($payload, AudioAttachmentRequest::make($payload)->getPayload());
    }

    public function testNewWithoutPayload(): void
    {
        $attachment = AudioAttachmentRequest::new();

        self::assertSame(AttachmentType::Audio, $attachment->getType());
        self::assertFalse($attachment->issetPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testPayloadIsValidatedOnEncode(): void
    {
        $attachment = new AudioAttachmentRequest(new UploadedInfo());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "token" field cannot be null.');

        self::assertNotFalse(json_encode($attachment));
    }

    public function testSetPayload(): void
    {
        $attachment = AudioAttachmentRequest::new();
        $payload = new UploadedInfo('audio-token');

        self::assertSame($attachment, $attachment->setPayload($payload));
        self::assertSame($payload, $attachment->getPayload());
        self::assertTrue($attachment->issetPayload());
    }
}
