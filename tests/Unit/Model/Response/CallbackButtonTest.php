<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Response\CallbackButton;

final class CallbackButtonTest extends Unit
{
    public function testGetters(): void
    {
        $button = CallbackButton::newFromData(['type' => 'callback', 'text' => 'Да', 'payload' => '{"action":"yes"}']);

        self::assertSame(ButtonType::Callback, $button->getType());
        self::assertSame('Да', $button->getText());
        self::assertSame('{"action":"yes"}', $button->getPayload());
    }
}
