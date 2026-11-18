<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Response\RequestGeoLocationButton;

final class RequestGeoLocationButtonTest extends Unit
{
    public function testGetters(): void
    {
        $button = RequestGeoLocationButton::newFromData([
            'type' => 'request_geo_location',
            'text' => 'Отправить геолокацию',
            'quick' => true,
        ]);

        self::assertSame(ButtonType::RequestGeoLocation, $button->getType());
        self::assertSame('Отправить геолокацию', $button->getText());
        self::assertTrue($button->isQuick());
    }

    public function testQuickIsFalseByDefault(): void
    {
        $button = RequestGeoLocationButton::newFromData([
            'type' => 'request_geo_location',
            'text' => 'Отправить геолокацию',
        ]);

        self::assertFalse($button->isQuick());
    }
}
