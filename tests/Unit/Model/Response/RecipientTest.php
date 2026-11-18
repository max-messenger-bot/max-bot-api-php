<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Response\Recipient;

final class RecipientTest extends Unit
{
    public function testChannelPost(): void
    {
        $recipient = Recipient::newFromData(['chat_id' => -100, 'chat_type' => 'channel', 'post_id' => 'mid.post']);

        self::assertSame(-100, $recipient->getChatId());
        self::assertSame(ChatType::Channel, $recipient->getChatType());
        self::assertSame('channel', $recipient->getChatTypeRaw());
        self::assertSame('mid.post', $recipient->getPostId());
        self::assertNull($recipient->getUserId());
    }

    public function testDialog(): void
    {
        $recipient = Recipient::newFromData(['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200]);

        self::assertSame(100, $recipient->getChatId());
        self::assertSame(ChatType::Dialog, $recipient->getChatType());
        self::assertSame('dialog', $recipient->getChatTypeRaw());
        self::assertSame(200, $recipient->getUserId());
        self::assertNull($recipient->getPostId());
    }

    public function testUnknownChatType(): void
    {
        $recipient = Recipient::newFromData(['chat_id' => 100, 'chat_type' => 'unknown_type']);

        self::assertNull($recipient->getChatType());
        self::assertSame('unknown_type', $recipient->getChatTypeRaw());
    }
}
