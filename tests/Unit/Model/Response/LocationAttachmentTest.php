<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\LocationAttachment;

final class LocationAttachmentTest extends Unit
{
    public function testGetters(): void
    {
        $attachment = LocationAttachment::newFromData([
            'type' => 'location',
            'latitude' => 55.755826,
            'longitude' => -37.6173,
        ]);

        self::assertSame(AttachmentType::Location, $attachment->getType());
        self::assertSame(55.755826, $attachment->getLatitude());
        self::assertSame(-37.6173, $attachment->getLongitude());
    }
}
