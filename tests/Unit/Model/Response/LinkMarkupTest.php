<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Response\LinkMarkup;

final class LinkMarkupTest extends Unit
{
    public function testGetters(): void
    {
        $markup = LinkMarkup::newFromData([
            'type' => 'link',
            'from' => 4,
            'length' => 6,
            'url' => 'https://example.test/page',
        ]);

        self::assertSame(MarkupElementType::Link, $markup->getType());
        self::assertSame(4, $markup->getFrom());
        self::assertSame(6, $markup->getLength());
        self::assertSame('https://example.test/page', $markup->getUrl());
    }
}
