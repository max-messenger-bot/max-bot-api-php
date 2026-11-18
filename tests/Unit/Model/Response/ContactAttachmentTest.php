<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\ContactAttachment;
use MaxMessenger\Bot\Model\Response\ContactAttachmentPayload;

final class ContactAttachmentTest extends Unit
{
    public function testGetPayload(): void
    {
        $attachment = ContactAttachment::newFromData([
            'type' => 'contact',
            'payload' => ['vcf_info' => "BEGIN:VCARD\nTEL:+79001234567\nEND:VCARD", 'hash' => 'contact-hash'],
        ]);

        self::assertSame(AttachmentType::Contact, $attachment->getType());

        $payload = $attachment->getPayload();
        self::assertInstanceOf(ContactAttachmentPayload::class, $payload);
        self::assertSame('contact-hash', $payload->getHash());
        self::assertSame($payload, $attachment->getPayload());
    }
}
