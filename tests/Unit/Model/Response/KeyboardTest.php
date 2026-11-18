<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\CallbackButton;
use MaxMessenger\Bot\Model\Response\Keyboard;
use MaxMessenger\Bot\Model\Response\LinkButton;
use MaxMessenger\Bot\Model\Response\MessageButton;

final class KeyboardTest extends Unit
{
    public function testGetButtons(): void
    {
        $keyboard = Keyboard::newFromData([
            'buttons' => [
                [
                    ['type' => 'callback', 'text' => 'Да', 'payload' => 'yes'],
                    ['type' => 'callback', 'text' => 'Нет', 'payload' => 'no'],
                ],
                [
                    ['type' => 'link', 'text' => 'Сайт', 'url' => 'https://example.test'],
                    ['type' => 'message', 'text' => 'Привет'],
                ],
            ],
        ]);

        $buttons = $keyboard->getButtons();

        self::assertCount(2, $buttons);
        self::assertCount(2, $buttons[0]);
        self::assertInstanceOf(CallbackButton::class, $buttons[0][0]);
        self::assertSame('yes', $buttons[0][0]->getPayload());
        self::assertInstanceOf(CallbackButton::class, $buttons[0][1]);
        self::assertSame('Нет', $buttons[0][1]->getText());
        self::assertInstanceOf(LinkButton::class, $buttons[1][0]);
        self::assertInstanceOf(MessageButton::class, $buttons[1][1]);
        self::assertSame($buttons, $keyboard->getButtons());
    }
}
