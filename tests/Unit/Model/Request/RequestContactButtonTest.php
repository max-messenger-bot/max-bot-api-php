<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\RequestContactButton;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class RequestContactButtonTest extends Unit
{
    public function testConstructor(): void
    {
        $button = new RequestContactButton('Поделиться контактом');

        self::assertSame(ButtonType::RequestContact, $button->getType());
        self::assertSame('Поделиться контактом', $button->getText());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"type":"request_contact","text":"Поделиться контактом"}',
            json_encode(RequestContactButton::make('Поделиться контактом'), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testMake(): void
    {
        self::assertSame('Контакт', RequestContactButton::make('Контакт')->getText());
    }

    public function testNew(): void
    {
        self::assertFalse(RequestContactButton::new()->issetText());
        self::assertSame('Контакт', RequestContactButton::new('Контакт')->getText());
    }
}
