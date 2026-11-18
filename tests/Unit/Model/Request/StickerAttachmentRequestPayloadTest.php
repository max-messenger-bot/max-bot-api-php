<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\StickerAttachmentRequestPayload;

use function json_encode;

final class StickerAttachmentRequestPayloadTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new StickerAttachmentRequestPayload('sticker-code');

        self::assertTrue($payload->issetCode());
        self::assertSame('sticker-code', $payload->getCode());
        self::assertSame('{"code":"sticker-code"}', json_encode($payload));
    }

    public function testEmptyCodeIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "code" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        new StickerAttachmentRequestPayload('');
    }

    public function testMake(): void
    {
        self::assertSame('sticker-code', StickerAttachmentRequestPayload::make('sticker-code')->getCode());
    }

    public function testNewWithoutCode(): void
    {
        $payload = StickerAttachmentRequestPayload::new();

        self::assertFalse($payload->issetCode());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "code" field cannot be null.');

        $payload->jsonSerialize();
    }

    public function testSetCode(): void
    {
        $payload = StickerAttachmentRequestPayload::new();

        self::assertSame($payload, $payload->setCode('sticker-code'));
        self::assertSame('sticker-code', $payload->getCode());
    }
}
