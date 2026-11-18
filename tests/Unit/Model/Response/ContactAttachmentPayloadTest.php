<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\ContactAttachmentPayload;
use MaxMessenger\Bot\Model\Response\User;

final class ContactAttachmentPayloadTest extends Unit
{
    public function testEmptyPayload(): void
    {
        $payload = ContactAttachmentPayload::newFromData([]);

        self::assertNull($payload->getHash());
        self::assertNull($payload->getMaxInfo());
        self::assertNull($payload->getMaxInfo());
        self::assertNull($payload->getPhones());
        self::assertNull($payload->getVcfInfo());
    }

    public function testGetMaxInfo(): void
    {
        $payload = ContactAttachmentPayload::newFromData([
            'max_info' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ]);

        $user = $payload->getMaxInfo();
        self::assertInstanceOf(User::class, $user);
        self::assertSame(200, $user->getUserId());
        self::assertSame('Иван', $user->getFirstName());
        self::assertSame($user, $payload->getMaxInfo());
    }

    public function testGetPhonesFromVcfInfo(): void
    {
        $vcfInfo = "BEGIN:VCARD\r\nVERSION:3.0\r\nFN:Иван Петров\r\n"
            . "TEL;TYPE=CELL:+7 (900) 123-45-67\r\n"
            . "tel;type=work:8.800.555-35-35\r\n"
            . 'END:VCARD';
        $payload = ContactAttachmentPayload::newFromData(['vcf_info' => $vcfInfo, 'hash' => 'contact-hash']);

        self::assertSame($vcfInfo, $payload->getVcfInfo());
        self::assertSame('contact-hash', $payload->getHash());
        self::assertSame(['+79001234567', '88005553535'], $payload->getPhones());
    }

    public function testGetPhonesWithoutPhones(): void
    {
        $payload = ContactAttachmentPayload::newFromData(['vcf_info' => "BEGIN:VCARD\nFN:Иван\nEND:VCARD"]);

        self::assertNull($payload->getPhones());
    }
}
