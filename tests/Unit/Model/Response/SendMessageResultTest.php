<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\SendMessageResult;

final class SendMessageResultTest extends Unit
{
    public function testGetMessage(): void
    {
        $result = SendMessageResult::newFromData([
            'message' => [
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.out', 'seq' => 1, 'text' => 'ok'],
            ],
        ]);

        $message = $result->getMessage();

        self::assertSame('mid.out', $message->getBody()->getMid());
        self::assertSame('ok', $message->getBody()->getText());
        self::assertSame($message, $result->getMessage());
    }
}
