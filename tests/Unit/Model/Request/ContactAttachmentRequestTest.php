<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\ContactAttachmentRequest;
use MaxMessenger\Bot\Model\Request\ContactAttachmentRequestPayload;

use function json_encode;

final class ContactAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new ContactAttachmentRequestPayload(200);
        $attachment = new ContactAttachmentRequest($payload);

        self::assertSame(AttachmentType::Contact, $attachment->getType());
        self::assertTrue($attachment->issetPayload());
        self::assertSame($payload, $attachment->getPayload());
        self::assertSame('{"type":"contact","payload":{"contact_id":200}}', json_encode($attachment));
    }

    public function testMake(): void
    {
        $payload = new ContactAttachmentRequestPayload(200);

        self::assertSame($payload, ContactAttachmentRequest::make($payload)->getPayload());
    }

    public function testNewWithoutPayload(): void
    {
        $attachment = ContactAttachmentRequest::new();

        self::assertSame(AttachmentType::Contact, $attachment->getType());
        self::assertFalse($attachment->issetPayload());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testPayloadIsValidatedOnEncode(): void
    {
        $attachment = new ContactAttachmentRequest(new ContactAttachmentRequestPayload());

        $this->expectException(RequiredOneFieldException::class);

        self::assertNotFalse(json_encode($attachment));
    }

    public function testSetPayload(): void
    {
        $attachment = ContactAttachmentRequest::new();
        $payload = new ContactAttachmentRequestPayload(200);

        self::assertSame($attachment, $attachment->setPayload($payload));
        self::assertSame($payload, $attachment->getPayload());
        self::assertTrue($attachment->issetPayload());
    }
}
