<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Response\ContactAttachmentPayload;

use function base64_encode;
use function bin2hex;
use function hash_hmac;
use function rtrim;
use function str_replace;
use function strtoupper;
use function strtr;

final class MaxApiClientContactHashTest extends Unit
{
    public function testAcceptsEscapedAndNormalizedLineEndings(): void
    {
        $client = new MaxApiClient('test-token');
        $crlf = "BEGIN:VCARD\r\nTEL:79990000000\r\nEND:VCARD\r\n";
        $lf = str_replace("\r\n", "\n", $crlf);
        $escaped = str_replace("\r\n", '\\r\\n', $crlf);

        $vcfVariants = [
            [$escaped, $crlf],
            [$escaped, $lf],
            [$crlf, $lf],
        ];

        foreach ($vcfVariants as [$receivedVcf, $signedVcf]) {
            $payload = ContactAttachmentPayload::newFromData([
                'vcf_info' => $receivedVcf,
                'hash' => hash_hmac('sha256', $signedVcf, 'test-token'),
            ]);

            self::assertTrue($client->validateContactAttachmentHash($payload));
        }
    }

    public function testAcceptsHexAndBase64Signatures(): void
    {
        $client = new MaxApiClient('test-token-0');
        $vcf = "BEGIN:VCARD\r\nTEL;TYPE=cell:79990000000\r\nEND:VCARD\r\n";
        $signature = hash_hmac('sha256', $vcf, 'test-token-0', true);
        $base64 = base64_encode($signature);
        $base64Url = strtr($base64, '+/', '-_');

        $hashes = [
            bin2hex($signature),
            strtoupper(bin2hex($signature)),
            $base64,
            rtrim($base64, '='),
            $base64Url,
            rtrim($base64Url, '='),
        ];

        foreach ($hashes as $hash) {
            $payload = ContactAttachmentPayload::newFromData([
                'vcf_info' => $vcf,
                'hash' => $hash,
            ]);

            self::assertTrue($client->validateContactAttachmentHash($payload));
        }
    }

    public function testRejectsMissingOrInvalidSignature(): void
    {
        $client = new MaxApiClient('test-token');
        $vcf = "BEGIN:VCARD\nTEL:79990000000\nEND:VCARD\n";

        $invalidData = [
            ['vcf_info' => $vcf],
            ['hash' => hash_hmac('sha256', $vcf, 'test-token')],
            ['vcf_info' => $vcf, 'hash' => 'invalid'],
            ['vcf_info' => $vcf, 'hash' => hash_hmac('sha256', $vcf, 'other-token')],
            [
                'vcf_info' => str_replace('79990000000', '79990000001', $vcf),
                'hash' => hash_hmac('sha256', $vcf, 'test-token'),
            ],
        ];

        foreach ($invalidData as $data) {
            $payload = ContactAttachmentPayload::newFromData($data);

            self::assertFalse($client->validateContactAttachmentHash($payload));
        }
    }
}
