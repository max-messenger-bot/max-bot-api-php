<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Response\UserMentionMarkup;

final class UserMentionMarkupTest extends Unit
{
    public function testMentionByLink(): void
    {
        $markup = UserMentionMarkup::newFromData([
            'type' => 'user_mention',
            'from' => 0,
            'length' => 5,
            'user_link' => '@ivan',
        ]);

        self::assertSame(MarkupElementType::UserMention, $markup->getType());
        self::assertSame('@ivan', $markup->getUserLink());
        self::assertNull($markup->getUserId());
    }

    public function testMentionByUserId(): void
    {
        $markup = UserMentionMarkup::newFromData([
            'type' => 'user_mention',
            'from' => 2,
            'length' => 4,
            'user_id' => 200,
        ]);

        self::assertSame(200, $markup->getUserId());
        self::assertNull($markup->getUserLink());
        self::assertSame(2, $markup->getFrom());
        self::assertSame(4, $markup->getLength());
    }
}
