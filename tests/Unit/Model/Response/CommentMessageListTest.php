<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\CommentMessageList;

final class CommentMessageListTest extends Unit
{
    public function testEmptyList(): void
    {
        self::assertSame([], CommentMessageList::newFromData(['messages' => []])->getMessages());
    }

    public function testGetMessages(): void
    {
        $list = CommentMessageList::newFromData([
            'messages' => [
                [
                    'recipient' => ['chat_id' => -100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
                    'timestamp' => 1_700_000_000_000,
                    'body' => ['mid' => 'mid.comment1', 'seq' => 1, 'text' => 'первый'],
                ],
                [
                    'recipient' => ['chat_id' => -100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
                    'timestamp' => 1_700_000_001_000,
                    'body' => ['mid' => 'mid.comment2', 'seq' => 2, 'text' => 'второй'],
                ],
            ],
        ]);

        $messages = $list->getMessages();

        self::assertCount(2, $messages);
        self::assertContainsOnlyInstancesOf(CommentMessage::class, $messages);
        self::assertSame('первый', $messages[0]->getText());
        self::assertSame('второй', $messages[1]->getText());
        self::assertSame($messages, $list->getMessages());
    }
}
