<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEditedEvent;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\Update;

final class MessageEditedEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetMessage(): void
    {
        $message = $this->createEvent()->getMessage();

        self::assertInstanceOf(Message::class, $message);
        self::assertSame('Привет', $message->getText());
    }

    public function testNoMessage(): void
    {
        self::assertNull($this->createEvent(withMessage: false)->getMessage());
    }

    public function testNoMessageChatId(): void
    {
        self::assertNull($this->createEvent(withMessage: false)->getChatId());
    }

    private function createEvent(bool $withMessage = true): MessageEditedEvent
    {
        $data = [
            'update_type' => 'message_edited',
            'timestamp' => 1_700_000_000_000,
        ];
        if ($withMessage) {
            $data['message'] = [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            ];
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(MessageEditedEvent::class, $event);

        return $event;
    }
}
