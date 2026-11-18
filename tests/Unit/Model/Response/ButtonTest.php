<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Response\Button;
use MaxMessenger\Bot\Model\Response\CallbackButton;
use MaxMessenger\Bot\Model\Response\ClipboardButton;
use MaxMessenger\Bot\Model\Response\LinkButton;
use MaxMessenger\Bot\Model\Response\MessageButton;
use MaxMessenger\Bot\Model\Response\OpenAppButton;
use MaxMessenger\Bot\Model\Response\RequestContactButton;
use MaxMessenger\Bot\Model\Response\RequestGeoLocationButton;

final class ButtonTest extends Unit
{
    /**
     * @return iterable<string, array{array{type: string, text: string, ...<string, string>}, class-string<Button>, ButtonType}>
     */
    public static function buttons(): iterable
    {
        yield 'callback' => [
            ['type' => 'callback', 'text' => 'Да', 'payload' => 'yes'],
            CallbackButton::class,
            ButtonType::Callback,
        ];
        yield 'clipboard' => [
            ['type' => 'clipboard', 'text' => 'Копировать', 'payload' => 'PROMO2026'],
            ClipboardButton::class,
            ButtonType::Clipboard,
        ];
        yield 'link' => [
            ['type' => 'link', 'text' => 'Сайт', 'url' => 'https://example.test'],
            LinkButton::class,
            ButtonType::Link,
        ];
        yield 'message' => [['type' => 'message', 'text' => 'Привет'], MessageButton::class, ButtonType::Message];
        yield 'open_app' => [
            ['type' => 'open_app', 'text' => 'Открыть приложение', 'web_app' => 'example_bot'],
            OpenAppButton::class,
            ButtonType::OpenApp,
        ];
        yield 'request_contact' => [
            ['type' => 'request_contact', 'text' => 'Отправить контакт'],
            RequestContactButton::class,
            ButtonType::RequestContact,
        ];
        yield 'request_geo_location' => [
            ['type' => 'request_geo_location', 'text' => 'Отправить геолокацию'],
            RequestGeoLocationButton::class,
            ButtonType::RequestGeoLocation,
        ];
    }

    /**
     * @param array{type: string, text: string, ...<string, string>} $data
     * @param class-string<Button> $class
     * @dataProvider buttons
     */
    public function testNewFromDataCreatesButtonByType(array $data, string $class, ButtonType $type): void
    {
        $button = Button::newFromData($data);

        self::assertInstanceOf($class, $button);
        self::assertSame($type, $button->getType());
        self::assertSame($type->value, $button->getTypeRaw());
        self::assertSame($data['text'], $button->getText());
    }

    public function testNewFromDataForUnknownType(): void
    {
        $button = Button::newFromData(['type' => 'hologram', 'text' => 'Голограмма']);

        self::assertSame(Button::class, $button::class);
        self::assertNull($button->getType());
        self::assertSame('hologram', $button->getTypeRaw());
        self::assertSame('Голограмма', $button->getText());
    }
}
