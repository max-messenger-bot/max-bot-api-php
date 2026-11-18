<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\MessageEditedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class MessageEditedUpdateTest extends Unit
{
    public function testGetMessage(): void
    {
        $update = $this->createUpdate([
            'message' => [
                'recipient' => ['chat_id' => 100, 'chat_type' => 'chat'],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 2, 'text' => 'Исправлено'],
            ],
        ]);

        $message = $update->getMessage();
        self::assertNotNull($message);
        self::assertSame('Исправлено', $message->getBody()->getText());
        self::assertSame($message, $update->getMessage());
    }

    public function testWithoutMessage(): void
    {
        $update = $this->createUpdate([]);

        self::assertFalse($update->isValid());
        self::assertSame(['message'], $update->getRequiredProperties());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdate(array $data): MessageEditedUpdate
    {
        $update = Update::newFromData($data + ['update_type' => 'message_edited', 'timestamp' => 1_700_000_000_000]);
        self::assertInstanceOf(MessageEditedUpdate::class, $update);

        return $update;
    }
}
