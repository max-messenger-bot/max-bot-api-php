<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Response\OpenAppButton;

final class OpenAppButtonTest extends Unit
{
    public function testGetters(): void
    {
        $button = OpenAppButton::newFromData([
            'type' => 'open_app',
            'text' => 'Открыть',
            'web_app' => 'example_bot',
            'contact_id' => 789,
            'payload' => 'start',
        ]);

        self::assertSame(ButtonType::OpenApp, $button->getType());
        self::assertSame('Открыть', $button->getText());
        self::assertSame('example_bot', $button->getWebApp());
        self::assertSame(789, $button->getContactId());
        self::assertSame('start', $button->getPayload());
    }

    public function testGettersWithoutOptionalFields(): void
    {
        $button = OpenAppButton::newFromData(['type' => 'open_app', 'text' => 'Открыть']);

        self::assertNull($button->getWebApp());
        self::assertNull($button->getContactId());
        self::assertNull($button->getPayload());
    }
}
