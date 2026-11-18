<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\ClipboardButton;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_UNICODE;

final class ClipboardButtonTest extends Unit
{
    public function testConstructor(): void
    {
        $button = new ClipboardButton('Скопировать', 'PROMO-2026');

        self::assertSame(ButtonType::Clipboard, $button->getType());
        self::assertSame('Скопировать', $button->getText());
        self::assertSame('PROMO-2026', $button->getPayload());
        self::assertTrue($button->issetPayload());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"type":"clipboard","text":"Скопировать","payload":"PROMO-2026"}',
            json_encode(ClipboardButton::make('Скопировать', 'PROMO-2026'), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testMake(): void
    {
        $button = ClipboardButton::make('Скопировать', 'PROMO-2026');

        self::assertSame('Скопировать', $button->getText());
        self::assertSame('PROMO-2026', $button->getPayload());
    }

    public function testNew(): void
    {
        $button = ClipboardButton::new();

        self::assertFalse($button->issetText());
        self::assertFalse($button->issetPayload());
        self::assertSame('PROMO-2026', ClipboardButton::new('Скопировать', 'PROMO-2026')->getPayload());
    }

    public function testPayloadIsRequired(): void
    {
        $button = new ClipboardButton('Скопировать');

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $button->jsonSerialize();
    }

    public function testSetPayload(): void
    {
        $button = new ClipboardButton('Скопировать');

        self::assertSame($button, $button->setPayload('PROMO-2026'));
        self::assertSame('PROMO-2026', $button->getPayload());
        self::assertSame(str_repeat('я', 1024), $button->setPayload(str_repeat('я', 1024))->getPayload());
    }

    public function testSetPayloadRejectsEmptyPayload(): void
    {
        $button = new ClipboardButton('Скопировать');

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "payload" expects a non-empty string, but an empty string was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        $button->setPayload('');
    }

    public function testSetPayloadRejectsTooLongPayload(): void
    {
        $button = new ClipboardButton('Скопировать');

        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "payload" expects a string of up to 1024 characters, but a string of 1025 characters was passed.',
        );

        $button->setPayload(str_repeat('я', 1025));
    }
}
