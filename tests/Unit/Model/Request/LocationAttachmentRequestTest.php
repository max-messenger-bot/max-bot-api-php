<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\LocationAttachmentRequest;

use function json_encode;

final class LocationAttachmentRequestTest extends Unit
{
    public function testConstructor(): void
    {
        $attachment = new LocationAttachmentRequest(55.75, 37.62);

        self::assertSame(AttachmentType::Location, $attachment->getType());
        self::assertTrue($attachment->issetLatitude());
        self::assertTrue($attachment->issetLongitude());
        self::assertSame(55.75, $attachment->getLatitude());
        self::assertSame(37.62, $attachment->getLongitude());
        self::assertSame('{"type":"location","latitude":55.75,"longitude":37.62}', json_encode($attachment));
    }

    public function testMake(): void
    {
        $attachment = LocationAttachmentRequest::make(55.75, 37.62);

        self::assertSame(55.75, $attachment->getLatitude());
        self::assertSame(37.62, $attachment->getLongitude());
    }

    public function testNewRequiresLatitude(): void
    {
        $attachment = LocationAttachmentRequest::new();

        self::assertFalse($attachment->issetLatitude());
        self::assertFalse($attachment->issetLongitude());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "latitude" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testNewRequiresLongitude(): void
    {
        $attachment = LocationAttachmentRequest::new(55.75);

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "longitude" field cannot be null.');

        $attachment->jsonSerialize();
    }

    public function testSetters(): void
    {
        $attachment = LocationAttachmentRequest::new();

        self::assertSame($attachment, $attachment->setLatitude(0.0));
        self::assertSame($attachment, $attachment->setLongitude(-0.5));
        self::assertSame(0.0, $attachment->getLatitude());
        self::assertSame(-0.5, $attachment->getLongitude());
        self::assertSame('{"type":"location","latitude":0,"longitude":-0.5}', json_encode($attachment));
    }
}
