<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\AttachmentPayload;
use MaxMessenger\Bot\Model\Response\FileAttachmentPayload;

final class FileAttachmentPayloadTest extends Unit
{
    public function testGetters(): void
    {
        $payload = FileAttachmentPayload::newFromData([
            'url' => 'https://example.test/report.pdf',
            'token' => 'file-token',
            'fileId' => 5549801968,
        ]);

        self::assertInstanceOf(AttachmentPayload::class, $payload);
        self::assertSame('https://example.test/report.pdf', $payload->getUrl());
        self::assertSame('file-token', $payload->getToken());
        self::assertSame(5549801968, $payload->getFileId());
    }
}
