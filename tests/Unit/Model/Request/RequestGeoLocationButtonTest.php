<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\RequestGeoLocationButton;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class RequestGeoLocationButtonTest extends Unit
{
    public function testConstructor(): void
    {
        $button = new RequestGeoLocationButton('Где я');

        self::assertSame(ButtonType::RequestGeoLocation, $button->getType());
        self::assertSame('Где я', $button->getText());
        self::assertFalse($button->isQuick());
        self::assertTrue((new RequestGeoLocationButton('Где я', true))->isQuick());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"type":"request_geo_location","text":"Где я","quick":false}',
            json_encode(RequestGeoLocationButton::make('Где я'), JSON_UNESCAPED_UNICODE),
        );
        self::assertSame(
            '{"type":"request_geo_location","text":"Где я","quick":true}',
            json_encode(RequestGeoLocationButton::make('Где я', true), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testMake(): void
    {
        $button = RequestGeoLocationButton::make('Где я', true);

        self::assertSame('Где я', $button->getText());
        self::assertTrue($button->isQuick());
    }

    public function testNew(): void
    {
        $button = RequestGeoLocationButton::new();

        self::assertFalse($button->issetText());
        self::assertFalse($button->isQuick());
        self::assertTrue(RequestGeoLocationButton::new('Где я', true)->isQuick());
    }

    public function testSetQuick(): void
    {
        $button = new RequestGeoLocationButton('Где я');

        self::assertSame($button, $button->setQuick(true));
        self::assertTrue($button->isQuick());
        self::assertFalse($button->setQuick(false)->isQuick());
    }
}
