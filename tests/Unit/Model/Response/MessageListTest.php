<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageList;

final class MessageListTest extends Unit
{
    public function testEmptyList(): void
    {
        self::assertSame([], MessageList::newFromData(['messages' => []])->getMessages());
    }

    public function testGetMessages(): void
    {
        $list = MessageList::newFromData([
            'messages' => [
                [
                    'recipient' => ['chat_id' => 100, 'chat_type' => 'chat'],
                    'timestamp' => 1_700_000_000_000,
                    'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'первое'],
                ],
                [
                    'recipient' => ['chat_id' => 100, 'chat_type' => 'chat'],
                    'timestamp' => 1_700_000_001_000,
                    'body' => ['mid' => 'mid.2', 'seq' => 2, 'text' => 'второе'],
                ],
            ],
        ]);

        $messages = $list->getMessages();

        self::assertCount(2, $messages);
        self::assertContainsOnlyInstancesOf(Message::class, $messages);
        self::assertSame('первое', $messages[0]->getText());
        self::assertSame('второе', $messages[1]->getText());
        self::assertSame($messages, $list->getMessages());
    }
}
