<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\MessageCreatedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class MessageCreatedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate([
            'message' => [
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            ],
            'user_locale' => 'ru',
        ]);

        $message = $update->getMessage();
        self::assertNotNull($message);
        self::assertSame('mid.1', $message->getBody()->getMid());
        self::assertSame($message, $update->getMessage());
        self::assertSame('ru', $update->getUserLocale());
    }

    public function testWithoutMessage(): void
    {
        $update = $this->createUpdate([]);

        self::assertFalse($update->isValid());
        self::assertSame(['message'], $update->getRequiredProperties());
    }

    public function testWithoutOptionalFields(): void
    {
        $update = $this->createUpdate(['message' => []]);

        self::assertTrue($update->isValid());
        self::assertNull($update->getUserLocale());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdate(array $data): MessageCreatedUpdate
    {
        $update = Update::newFromData($data + ['update_type' => 'message_created', 'timestamp' => 1_700_000_000_000]);
        self::assertInstanceOf(MessageCreatedUpdate::class, $update);

        return $update;
    }
}
