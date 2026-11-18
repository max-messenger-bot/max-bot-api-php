<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Response\ClipboardButton;

final class ClipboardButtonTest extends Unit
{
    public function testGetters(): void
    {
        $button = ClipboardButton::newFromData([
            'type' => 'clipboard',
            'text' => 'Копировать',
            'payload' => 'PROMO2026',
        ]);

        self::assertSame(ButtonType::Clipboard, $button->getType());
        self::assertSame('Копировать', $button->getText());
        self::assertSame('PROMO2026', $button->getPayload());
    }
}
