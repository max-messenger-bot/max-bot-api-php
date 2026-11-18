<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Response\LinkedMessage;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageBody;
use MaxMessenger\Bot\Model\Response\MessageStat;
use MaxMessenger\Bot\Model\Response\Recipient;
use MaxMessenger\Bot\Model\Response\User;

final class MessageTest extends Unit
{
    public function testFullMessage(): void
    {
        $message = Message::newFromData([
            'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'recipient' => ['chat_id' => -100, 'chat_type' => 'channel'],
            'timestamp' => 1_700_000_000_000,
            'link' => [
                'type' => 'reply',
                'chat_id' => -100,
                'message' => ['mid' => 'mid.parent', 'seq' => 1, 'text' => 'родительское'],
            ],
            'body' => ['mid' => 'mid.1', 'seq' => 2, 'text' => 'Привет'],
            'stat' => ['views' => 15],
            'url' => 'https://max.ru/c/-100/mid.1',
        ]);

        $sender = $message->getSender();
        self::assertInstanceOf(User::class, $sender);
        self::assertSame(200, $sender->getUserId());
        self::assertInstanceOf(Recipient::class, $message->getRecipient());
        self::assertSame(ChatType::Channel, $message->getRecipient()->getChatType());
        self::assertSame(1_700_000_000_000, $message->getTimestampRaw());
        self::assertSame('2023-11-14 22:13:20', $message->getTimestamp()->format('Y-m-d H:i:s'));
        $link = $message->getLink();
        self::assertInstanceOf(LinkedMessage::class, $link);
        self::assertTrue($link->isReply());
        self::assertInstanceOf(MessageBody::class, $message->getBody());
        self::assertSame('mid.1', $message->getBody()->getMid());
        self::assertSame('Привет', $message->getText());
        $stat = $message->getStat();
        self::assertInstanceOf(MessageStat::class, $stat);
        self::assertSame(15, $stat->getViews());
        self::assertSame('https://max.ru/c/-100/mid.1', $message->getUrl());
    }

    public function testMinimalMessage(): void
    {
        $message = Message::newFromData([
            'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => ''],
        ]);

        self::assertNull($message->getSender());
        self::assertNull($message->getLink());
        self::assertNull($message->getStat());
        self::assertNull($message->getUrl());
        self::assertSame('', $message->getText());
    }

    public function testNestedModelsAreCreatedOnce(): void
    {
        $message = Message::newFromData([
            'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog'],
            'timestamp' => 1_700_000_000_000,
            'link' => [
                'type' => 'forward',
                'chat_id' => 300,
                'message' => ['mid' => 'mid.source', 'seq' => 1, 'text' => 'пересланное'],
            ],
            'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            'stat' => ['views' => 1],
        ]);

        self::assertSame($message->getBody(), $message->getBody());
        self::assertSame($message->getLink(), $message->getLink());
        self::assertSame($message->getRecipient(), $message->getRecipient());
        self::assertSame($message->getSender(), $message->getSender());
        self::assertSame($message->getStat(), $message->getStat());
    }
}
