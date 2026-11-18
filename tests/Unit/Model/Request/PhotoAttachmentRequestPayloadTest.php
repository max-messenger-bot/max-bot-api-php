<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinItemsException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Model\Request\PhotoAttachmentRequestPayload;
use MaxMessenger\Bot\Model\Response\PhotoToken;
use MaxMessenger\Bot\Model\Response\PhotoTokens;

use function json_encode;

use const JSON_UNESCAPED_SLASHES;

final class PhotoAttachmentRequestPayloadTest extends Unit
{
    public function testConstructorPrefersUrlOverToken(): void
    {
        $payload = new PhotoAttachmentRequestPayload('https://example.test/a.png', 'photo-token');

        self::assertSame('https://example.test/a.png', $payload->getUrl());
        self::assertFalse($payload->issetToken());
        self::assertNull($payload->getToken());
    }

    public function testEmptyTokenIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "token" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        PhotoAttachmentRequestPayload::newWithToken('');
    }

    public function testEmptyUrlIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "url" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        PhotoAttachmentRequestPayload::newWithUrl('');
    }

    public function testMake(): void
    {
        self::assertSame('photo-token', PhotoAttachmentRequestPayload::make(token: 'photo-token')->getToken());
        self::assertSame(
            'https://example.test/a.png',
            PhotoAttachmentRequestPayload::make('https://example.test/a.png')->getUrl(),
        );
    }

    public function testNewRequiresUrlOrTokenOrPhotos(): void
    {
        $payload = PhotoAttachmentRequestPayload::new();

        self::assertFalse($payload->issetUrl());
        self::assertFalse($payload->issetToken());
        self::assertFalse($payload->issetPhotos());
        self::assertNull($payload->getPhotos());

        $this->expectException(RequiredOneFieldException::class);
        $this->expectExceptionMessage('At least one field of "url", "token", "photos" cannot be null.');

        $payload->jsonSerialize();
    }

    public function testNewWithPhotosFromUploadResponse(): void
    {
        $uploaded = PhotoTokens::newFromData(['photos' => ['photo-id' => ['token' => 'photo-token']]]);
        $photos = $uploaded->getPhotos();
        $payload = PhotoAttachmentRequestPayload::newWithPhotos($photos);

        self::assertSame($photos, $payload->getPhotos());
        self::assertTrue($payload->issetPhotos());
        self::assertFalse($payload->issetUrl());
        self::assertFalse($payload->issetToken());
        self::assertSame('{"photos":{"photo-id":{"token":"photo-token"}}}', json_encode($payload));
    }

    public function testNewWithToken(): void
    {
        $payload = PhotoAttachmentRequestPayload::newWithToken('photo-token');

        self::assertTrue($payload->issetToken());
        self::assertSame('photo-token', $payload->getToken());
        self::assertFalse($payload->issetUrl());
        self::assertNull($payload->getUrl());
        self::assertSame('{"token":"photo-token"}', json_encode($payload));
    }

    public function testNewWithUrl(): void
    {
        $payload = PhotoAttachmentRequestPayload::newWithUrl('https://example.test/a.png');

        self::assertTrue($payload->issetUrl());
        self::assertSame('https://example.test/a.png', $payload->getUrl());
        self::assertFalse($payload->issetToken());
        self::assertSame('{"url":"https://example.test/a.png"}', json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    public function testPhotosAreMutuallyExclusiveWithUrlAndToken(): void
    {
        $photos = ['photo-id' => ['token' => 'photo-token']];
        $payload = PhotoAttachmentRequestPayload::newWithUrl('https://example.test/a.png');

        self::assertSame($payload, $payload->setPhotos($photos));
        self::assertNull($payload->getUrl());
        self::assertNull($payload->getToken());
        self::assertFalse($payload->issetUrl());

        $payload->setToken('other-token');
        self::assertFalse($payload->issetPhotos());
        self::assertNull($payload->getPhotos());

        $payload->setPhotos($photos);
        self::assertFalse($payload->issetToken());

        $payload->setUrl('https://example.test/a.png');
        self::assertFalse($payload->issetPhotos());
        self::assertNull($payload->getPhotos());
    }

    public function testPhotosWithStringKeysSerializeAsObject(): void
    {
        $photos = [
            'raw-photo' => ['token' => 'raw-token'],
            'model-photo' => PhotoToken::newFromData(['token' => 'model-token']),
        ];
        $payload = PhotoAttachmentRequestPayload::make(photos: $photos);

        self::assertSame($photos, $payload->getPhotos());
        self::assertIsObject($payload->getRawData()['photos']);
        self::assertSame(
            '{"photos":{"raw-photo":{"token":"raw-token"},"model-photo":{"token":"model-token"}}}',
            json_encode($payload),
        );
    }

    public function testSetPhotosIdentifiesInvalidModelToken(): void
    {
        $payload = PhotoAttachmentRequestPayload::new();

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "photos[broken-model].token" expects a non-empty string, but an empty string was passed.',
        );

        $payload->setPhotos(['broken-model' => PhotoToken::newFromData(['token' => ''])]);
    }

    public function testSetPhotosIdentifiesInvalidToken(): void
    {
        $payload = PhotoAttachmentRequestPayload::new();

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "photos[broken-photo].token" expects a non-empty string, but an empty string was passed.',
        );

        /** @psalm-suppress ArgumentTypeCoercion */
        $payload->setPhotos(['valid-photo' => ['token' => 'photo-token'], 'broken-photo' => ['token' => '']]);
    }

    public function testSetPhotosRejectsEmptyArray(): void
    {
        $payload = PhotoAttachmentRequestPayload::new();

        $this->expectException(MinItemsException::class);
        $this->expectExceptionMessage('Argument "photos" expects a non-empty array, but an empty array was passed.');

        /** @psalm-suppress InvalidArgument */
        $payload->setPhotos([]);
    }

    public function testSetTokenReplacesUrl(): void
    {
        $payload = PhotoAttachmentRequestPayload::newWithUrl('https://example.test/a.png');

        self::assertSame($payload, $payload->setToken('photo-token'));
        self::assertFalse($payload->issetUrl());
        self::assertSame('{"token":"photo-token"}', json_encode($payload));
    }

    public function testSetUrlReplacesToken(): void
    {
        $payload = PhotoAttachmentRequestPayload::newWithToken('photo-token');

        self::assertSame($payload, $payload->setUrl('https://example.test/a.png'));
        self::assertFalse($payload->issetToken());
        self::assertSame('{"url":"https://example.test/a.png"}', json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
