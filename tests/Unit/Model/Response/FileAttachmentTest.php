<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\FileAttachment;
use MaxMessenger\Bot\Model\Response\FileAttachmentPayload;

final class FileAttachmentTest extends Unit
{
    public function testGetters(): void
    {
        $attachment = FileAttachment::newFromData([
            'type' => 'file',
            'payload' => ['url' => 'https://example.test/report.pdf', 'token' => 'file-token', 'fileId' => 123],
            'filename' => 'report.pdf',
            'size' => 1048576,
        ]);

        self::assertSame(AttachmentType::File, $attachment->getType());
        self::assertSame('report.pdf', $attachment->getFilename());
        self::assertSame(1048576, $attachment->getSize());

        $payload = $attachment->getPayload();
        self::assertInstanceOf(FileAttachmentPayload::class, $payload);
        self::assertSame($payload, $attachment->getPayload());
    }
}
