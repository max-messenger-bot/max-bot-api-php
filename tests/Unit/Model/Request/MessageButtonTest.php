<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\MessageButton;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class MessageButtonTest extends Unit
{
    public function testConstructor(): void
    {
        $button = new MessageButton('Привет');

        self::assertSame(ButtonType::Message, $button->getType());
        self::assertSame('Привет', $button->getText());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"type":"message","text":"Привет"}',
            json_encode(MessageButton::make('Привет'), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testMake(): void
    {
        self::assertSame('Привет', MessageButton::make('Привет')->getText());
    }

    public function testNew(): void
    {
        self::assertFalse(MessageButton::new()->issetText());
        self::assertSame('Привет', MessageButton::new('Привет')->getText());
    }
}
