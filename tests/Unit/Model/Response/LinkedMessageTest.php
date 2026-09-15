<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Response\LinkedMessage;
use MaxMessenger\Bot\Model\Response\MessageBody;

final class LinkedMessageTest extends Unit
{
    public function testForward(): void
    {
        $link = LinkedMessage::newFromData([
            'type' => 'forward',
            'sender' => ['user_id' => 200, 'first_name' => 'Пользователь', 'is_bot' => false],
            'chat_id' => -100,
            'message' => ['mid' => 'mid.source', 'seq' => 2, 'text' => 'пересланное'],
        ]);

        self::assertSame(MessageLinkType::Forward, $link->getType());
        self::assertSame('forward', $link->getTypeRaw());
        self::assertTrue($link->isForward());
        self::assertFalse($link->isReply());
        self::assertSame(-100, $link->getChatId());
        self::assertSame('Пользователь', $link->getSender()?->getFirstName());
    }

    public function testForwardFromInaccessibleChat(): void
    {
        $link = LinkedMessage::newFromData([
            'type' => 'forward',
            'chat_id' => 0,
            'message' => ['mid' => 'mid.source', 'seq' => 3, 'text' => 'из недоступного чата'],
        ]);

        self::assertNull($link->getChatId());
        self::assertNull($link->getSender());
    }

    public function testMessage(): void
    {
        $body = LinkedMessage::newFromData([
            'type' => 'reply',
            'chat_id' => 100,
            'message' => ['mid' => 'mid.parent', 'seq' => 1, 'text' => 'родительское'],
        ])->getMessage();

        self::assertInstanceOf(MessageBody::class, $body);
        self::assertSame('mid.parent', $body->getMid());
        self::assertSame('родительское', $body->getText());
    }

    public function testReply(): void
    {
        $link = LinkedMessage::newFromData([
            'type' => 'reply',
            'sender' => ['user_id' => 200, 'first_name' => 'Пользователь', 'is_bot' => false],
            'chat_id' => 100,
            'message' => ['mid' => 'mid.parent', 'seq' => 1, 'text' => 'родительское'],
        ]);

        self::assertSame(MessageLinkType::Reply, $link->getType());
        self::assertTrue($link->isReply());
        self::assertFalse($link->isForward());
        self::assertSame(100, $link->getChatId());
    }

    public function testUnknownType(): void
    {
        $link = LinkedMessage::newFromData([
            'type' => 'unknown_type',
            'chat_id' => 100,
            'message' => ['mid' => 'mid.parent', 'seq' => 1, 'text' => 'родительское'],
        ]);

        self::assertNull($link->getType());
        self::assertSame('unknown_type', $link->getTypeRaw());
        self::assertFalse($link->isForward());
        self::assertFalse($link->isReply());
    }
}
