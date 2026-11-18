<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\SendCommentResult;

final class SendCommentResultTest extends Unit
{
    public function testGetMessage(): void
    {
        $result = SendCommentResult::newFromData([
            'message' => [
                'recipient' => ['chat_id' => 100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            ],
        ]);

        $message = $result->getMessage();

        self::assertSame('mid.comment', $message->getBody()->getMid());
        self::assertSame('комментарий', $message->getText());
        self::assertSame($message, $result->getMessage());
    }
}
