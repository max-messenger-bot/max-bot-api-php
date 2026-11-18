<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\ShareAttachmentPayload;

use function json_encode;

use const JSON_UNESCAPED_SLASHES;

final class ShareAttachmentPayloadTest extends Unit
{
    public function testConstructor(): void
    {
        $payload = new ShareAttachmentPayload('https://example.test/', 'share-token');

        self::assertTrue($payload->issetUrl());
        self::assertSame('https://example.test/', $payload->getUrl());
        self::assertTrue($payload->issetToken());
        self::assertSame('share-token', $payload->getToken());
        self::assertSame(
            '{"url":"https://example.test/","token":"share-token"}',
            json_encode($payload, JSON_UNESCAPED_SLASHES),
        );
    }

    public function testConstructorWithoutToken(): void
    {
        $payload = new ShareAttachmentPayload('https://example.test/');

        self::assertFalse($payload->issetToken());
        self::assertNull($payload->getToken());
        self::assertSame('{"url":"https://example.test/"}', json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    public function testEmptyTokenIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "token" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        new ShareAttachmentPayload('https://example.test/', '');
    }

    public function testEmptyUrlIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "url" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        new ShareAttachmentPayload('');
    }

    public function testMake(): void
    {
        $payload = ShareAttachmentPayload::make('https://example.test/', 'share-token');

        self::assertSame('https://example.test/', $payload->getUrl());
        self::assertSame('share-token', $payload->getToken());
    }

    public function testNewRequiresUrl(): void
    {
        $payload = ShareAttachmentPayload::new();

        self::assertFalse($payload->issetUrl());
        self::assertFalse($payload->issetToken());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "url" field cannot be null.');

        $payload->jsonSerialize();
    }

    public function testSettersAndUnsetToken(): void
    {
        $payload = ShareAttachmentPayload::new();

        self::assertSame($payload, $payload->setUrl('https://example.test/'));
        self::assertSame($payload, $payload->setToken('share-token'));
        self::assertSame('share-token', $payload->getToken());

        self::assertSame($payload, $payload->unsetToken());
        self::assertFalse($payload->issetToken());
        self::assertSame('{"url":"https://example.test/"}', json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    public function testTokenWithoutUrlIsRejected(): void
    {
        $payload = ShareAttachmentPayload::new(token: 'share-token');

        self::assertFalse($payload->issetUrl());
        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "url" field cannot be null.');

        $payload->jsonSerialize();
    }
}
