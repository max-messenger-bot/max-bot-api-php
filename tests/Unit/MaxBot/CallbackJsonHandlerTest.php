<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\Model\Response\Update;

final class CallbackJsonHandlerTest extends Unit
{
    public function testActionTooLong(): void
    {
        $event = $this->createEvent('{"cmd":"abc"}');
        $handler = (new CallbackJsonHandler('cmd'))->setActionMaxLength(2);

        self::assertFalse($handler->handle($event));
    }

    public function testGetters(): void
    {
        $handler = new CallbackJsonHandler('cmd');

        self::assertSame('cmd', $handler->getActionKey());
        self::assertSame(64, $handler->getActionMaxLength());

        self::assertSame($handler, $handler->setActionMaxLength(5));
        self::assertSame(5, $handler->getActionMaxLength());
    }

    public function testHandleMatchingAction(): void
    {
        $event = $this->createEvent('{"cmd":"act"}');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertTrue($handler->handle($event));
        self::assertSame('act', $event->userData['__action']);
    }

    public function testHandleMissingKeyReturnsFalse(): void
    {
        $event = $this->createEvent('{"other":"x"}');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandleNonJsonReturnsFalse(): void
    {
        $event = $this->createEvent('plain');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    private function createEvent(string $payload): MessageCallbackEvent
    {
        $update = Update::newFromData([
            'update_type' => 'message_callback',
            'timestamp' => 1_700_000_000_000,
            'callback' => [
                'timestamp' => 1_700_000_000_000,
                'callback_id' => 'cb.1',
                'payload' => $payload,
                'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            ],
            'message' => [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            ],
        ]);

        $event = BaseEvent::new($update, new MaxApiClient('test-token'), []);
        self::assertInstanceOf(MessageCallbackEvent::class, $event);

        return $event;
    }
}
