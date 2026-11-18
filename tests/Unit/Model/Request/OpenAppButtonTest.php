<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\OpenAppButton;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_UNICODE;

final class OpenAppButtonTest extends Unit
{
    public function testConstructor(): void
    {
        $button = new OpenAppButton('Открыть', 'my_bot', 123_456, 'start');

        self::assertSame(ButtonType::OpenApp, $button->getType());
        self::assertSame('Открыть', $button->getText());
        self::assertSame('my_bot', $button->getWebApp());
        self::assertSame(123_456, $button->getContactId());
        self::assertSame('start', $button->getPayload());
        self::assertTrue($button->issetWebApp());
        self::assertTrue($button->issetContactId());
        self::assertTrue($button->issetPayload());
    }

    public function testConstructorWithTextOnly(): void
    {
        $button = new OpenAppButton('Открыть');

        self::assertNull($button->getContactId());
        self::assertNull($button->getPayload());
        self::assertFalse($button->issetWebApp());
        self::assertFalse($button->issetContactId());
        self::assertFalse($button->issetPayload());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"type":"open_app","text":"Открыть","web_app":"my_bot","contact_id":123456,"payload":"start"}',
            json_encode(OpenAppButton::make('Открыть', 'my_bot', 123_456, 'start'), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testJsonSerializeWithTextOnly(): void
    {
        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "web_app" field cannot be null.');
        OpenAppButton::new('Открыть')->jsonSerialize();
    }

    public function testMake(): void
    {
        $button = OpenAppButton::make('Открыть', 'my_bot', contactId: 123_456);

        self::assertSame('Открыть', $button->getText());
        self::assertSame('my_bot', $button->getWebApp());
        self::assertSame(123_456, $button->getContactId());
    }

    public function testNew(): void
    {
        $button = OpenAppButton::new();

        self::assertFalse($button->issetText());
        self::assertFalse($button->issetWebApp());
        self::assertSame('start', OpenAppButton::new('Открыть', payload: 'start')->getPayload());
    }

    public function testPayloadAtMaximumLength(): void
    {
        $payload = str_repeat('a', 512);
        $button = OpenAppButton::make('Открыть', 'my_bot', payload: $payload);

        self::assertSame($payload, $button->getPayload());
        $button->jsonSerialize();
    }

    public function testPayloadRejectsInvalidCharacters(): void
    {
        $this->expectException(MatchException::class);
        OpenAppButton::new()->setPayload('invalid payload');
    }

    public function testPayloadRejectsTooLongValue(): void
    {
        $this->expectException(MaxLengthException::class);
        OpenAppButton::new()->setPayload(str_repeat('a', 513));
    }

    public function testSetAndUnsetContactId(): void
    {
        $button = new OpenAppButton('Открыть');

        self::assertSame($button, $button->setContactId(123_456));
        self::assertSame(123_456, $button->getContactId());
        self::assertSame($button, $button->unsetContactId());
        self::assertFalse($button->issetContactId());
        self::assertNull($button->getContactId());
    }

    public function testSetAndUnsetPayload(): void
    {
        $button = new OpenAppButton('Открыть');

        self::assertSame($button, $button->setPayload('s'));
        self::assertSame('s', $button->getPayload());
        self::assertSame($button, $button->unsetPayload());
        self::assertFalse($button->issetPayload());
        self::assertNull($button->getPayload());
    }

    public function testSetPayloadRejectsEmptyPayload(): void
    {
        $button = new OpenAppButton('Открыть');

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "payload" expects a non-empty string, but an empty string was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        $button->setPayload('');
    }

    public function testSetWebApp(): void
    {
        $button = new OpenAppButton('Открыть');

        self::assertSame($button, $button->setWebApp('a'));
        self::assertSame('a', $button->getWebApp());
        self::assertTrue($button->issetWebApp());
    }

    public function testSetWebAppRejectsTooShortName(): void
    {
        $button = new OpenAppButton('Открыть');

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "webApp" expects a non-empty string, but an empty string was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        $button->setWebApp('');
    }
}
