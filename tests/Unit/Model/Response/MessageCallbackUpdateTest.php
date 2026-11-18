<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\MessageCallbackUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class MessageCallbackUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate(['user_locale' => 'ru']);

        $callback = $update->getCallback();
        self::assertSame('cb.1', $callback->getCallbackId());
        self::assertSame('payload', $callback->getPayload());
        self::assertSame(200, $callback->getUser()->getUserId());
        self::assertSame($callback, $update->getCallback());

        $message = $update->getMessage();
        self::assertNotNull($message);
        self::assertSame('mid.1', $message->getBody()->getMid());
        self::assertSame($message, $update->getMessage());

        self::assertSame('ru', $update->getUserLocale());
    }

    public function testWithoutMessage(): void
    {
        $data = $this->createUpdate([])->getRawData();
        unset($data['message']);
        $update = MessageCallbackUpdate::newFromData($data);

        self::assertFalse($update->isValid());
        self::assertSame(['callback', 'message'], $update->getRequiredProperties());
    }

    public function testWithoutUserLocale(): void
    {
        self::assertNull($this->createUpdate([])->getUserLocale());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdate(array $data): MessageCallbackUpdate
    {
        $update = Update::newFromData($data + [
            'update_type' => 'message_callback',
            'timestamp' => 1_700_000_000_000,
            'callback' => [
                'timestamp' => 1_700_000_000_000,
                'callback_id' => 'cb.1',
                'payload' => 'payload',
                'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            ],
            'message' => [
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            ],
        ]);
        self::assertInstanceOf(MessageCallbackUpdate::class, $update);

        return $update;
    }
}
