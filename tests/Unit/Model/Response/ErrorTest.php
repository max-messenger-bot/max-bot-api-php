<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\Error;

final class ErrorTest extends Unit
{
    public function testGetters(): void
    {
        $error = Error::newFromData([
            'code' => 'chat.not.found',
            'message' => 'Chat not found',
            'error' => 'not.found',
        ]);

        self::assertSame('chat.not.found', $error->getCode());
        self::assertSame('Chat not found', $error->getMessage());
        self::assertSame('not.found', $error->getError());
        self::assertTrue($error->isValid());
    }

    public function testIsAttachmentNotReadyByCode(): void
    {
        $error = Error::newFromData(['code' => 'attachment.not.ready', 'message' => 'Attachment not ready']);

        self::assertTrue($error->isAttachmentNotReady());
    }

    public function testIsAttachmentNotReadyByMessage(): void
    {
        $error = Error::newFromData([
            'code' => 'proto.payload',
            'message' => 'attachment.file.not.processed: File is still processing',
        ]);

        self::assertTrue($error->isAttachmentNotReady());
    }

    public function testIsAttachmentNotReadyForOtherError(): void
    {
        $error = Error::newFromData(['code' => 'proto.payload', 'message' => 'Bad request']);

        self::assertFalse($error->isAttachmentNotReady());
    }

    public function testIsValidWithoutMessage(): void
    {
        self::assertFalse(Error::newFromData(['code' => 'proto.payload'])->isValid());
    }
}
