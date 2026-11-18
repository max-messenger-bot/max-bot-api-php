<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Model\Request\ContactAttachmentRequestPayload;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class ContactAttachmentRequestPayloadTest extends Unit
{
    private const VCF = "BEGIN:VCARD\nVERSION:3.0\nFN:Иван\nEND:VCARD";

    public function testConstructorWithContactId(): void
    {
        $payload = new ContactAttachmentRequestPayload(200);

        self::assertTrue($payload->issetContactId());
        self::assertSame(200, $payload->getContactId());
        self::assertFalse($payload->issetVcfInfo());
        self::assertNull($payload->getVcfInfo());
        self::assertSame('{"contact_id":200}', json_encode($payload));
    }

    public function testConstructorWithContactIdAndVcfInfo(): void
    {
        $payload = new ContactAttachmentRequestPayload(200, self::VCF);

        self::assertSame(
            '{"contact_id":200,"vcf_info":"BEGIN:VCARD\nVERSION:3.0\nFN:Иван\nEND:VCARD"}',
            json_encode($payload, JSON_UNESCAPED_UNICODE),
        );
    }

    public function testConstructorWithVcfInfo(): void
    {
        $payload = new ContactAttachmentRequestPayload(vcfInfo: self::VCF);

        self::assertFalse($payload->issetContactId());
        self::assertNull($payload->getContactId());
        self::assertTrue($payload->issetVcfInfo());
        self::assertSame(self::VCF, $payload->getVcfInfo());
    }

    public function testMake(): void
    {
        $payload = ContactAttachmentRequestPayload::make(200, self::VCF);

        self::assertSame(200, $payload->getContactId());
        self::assertSame(self::VCF, $payload->getVcfInfo());
    }

    public function testNewRequiresContactIdOrVcfInfo(): void
    {
        $payload = ContactAttachmentRequestPayload::new();

        self::assertFalse($payload->issetContactId());
        self::assertFalse($payload->issetVcfInfo());

        $this->expectException(RequiredOneFieldException::class);
        $this->expectExceptionMessage('At least one field of "contact_id", "vcf_info" cannot be null.');

        $payload->jsonSerialize();
    }

    public function testSettersAndUnsetters(): void
    {
        $payload = ContactAttachmentRequestPayload::new();

        self::assertSame($payload, $payload->setContactId(300));
        self::assertSame($payload, $payload->setVcfInfo(self::VCF));
        self::assertSame(300, $payload->getContactId());
        self::assertSame(self::VCF, $payload->getVcfInfo());

        self::assertSame($payload, $payload->unsetContactId());
        self::assertFalse($payload->issetContactId());
        self::assertSame(self::VCF, $payload->getVcfInfo());

        self::assertSame($payload, $payload->unsetVcfInfo());
        self::assertFalse($payload->issetVcfInfo());
    }

    public function testShortVcfInfoIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "vcfInfo" expects a string of at least 20 characters, but a string of 11 characters was passed.',
        );

        new ContactAttachmentRequestPayload(vcfInfo: 'BEGIN:VCARD');
    }
}
