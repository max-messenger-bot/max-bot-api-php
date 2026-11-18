<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\CommentCreatedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class CommentCreatedUpdateTest extends Unit
{
    public function testGetMessage(): void
    {
        $update = Update::newFromData([
            'update_type' => 'comment_created',
            'timestamp' => 1_700_000_000_000,
            'message' => [
                'recipient' => ['chat_id' => 100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            ],
        ]);
        self::assertInstanceOf(CommentCreatedUpdate::class, $update);

        $message = $update->getMessage();

        self::assertSame('комментарий', $message->getText());
        self::assertSame('mid.comment', $message->getBody()->getMid());
        self::assertSame('mid.post', $message->getRecipient()->getPostId());
        self::assertSame($message, $update->getMessage());
    }
}
