<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Response\CommentLinkedMessage;
use MaxMessenger\Bot\Model\Response\CommentMessageBody;

final class CommentLinkedMessageTest extends Unit
{
    public function testForwardWithSender(): void
    {
        $link = CommentLinkedMessage::newFromData([
            'type' => 'forward',
            'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'chat_id' => -100,
            'message' => ['mid' => 'mid.source', 'seq' => 3, 'text' => 'пересланный комментарий'],
        ]);

        self::assertSame(MessageLinkType::Forward, $link->getType());
        self::assertSame('forward', $link->getTypeRaw());
        self::assertTrue($link->isForward());
        self::assertFalse($link->isReply());
        self::assertSame(-100, $link->getChatId());
        self::assertSame(200, $link->getSender()?->getUserId());
        self::assertSame($link->getSender(), $link->getSender());

        $message = $link->getMessage();
        self::assertInstanceOf(CommentMessageBody::class, $message);
        self::assertSame('mid.source', $message->getMid());
        self::assertSame('пересланный комментарий', $message->getText());
        self::assertSame($message, $link->getMessage());
    }

    public function testReplyWithoutSender(): void
    {
        $link = CommentLinkedMessage::newFromData([
            'type' => 'reply',
            'chat_id' => -100,
            'message' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'исходный комментарий'],
        ]);

        self::assertSame(MessageLinkType::Reply, $link->getType());
        self::assertSame('reply', $link->getTypeRaw());
        self::assertTrue($link->isReply());
        self::assertFalse($link->isForward());
        self::assertNull($link->getSender());
    }

    public function testUnknownType(): void
    {
        $link = CommentLinkedMessage::newFromData([
            'type' => 'unknown_type',
            'chat_id' => -100,
            'message' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
        ]);

        self::assertNull($link->getType());
        self::assertSame('unknown_type', $link->getTypeRaw());
        self::assertFalse($link->isForward());
        self::assertFalse($link->isReply());
    }
}
