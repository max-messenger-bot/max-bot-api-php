<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Response\LinkButton;

final class LinkButtonTest extends Unit
{
    public function testGetters(): void
    {
        $button = LinkButton::newFromData(['type' => 'link', 'text' => 'Сайт', 'url' => 'https://example.test/page']);

        self::assertSame(ButtonType::Link, $button->getType());
        self::assertSame('Сайт', $button->getText());
        self::assertSame('https://example.test/page', $button->getUrl());
    }
}
