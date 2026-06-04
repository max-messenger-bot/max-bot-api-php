<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\Model\Response\Update;

final class CallbackHandlerTest extends Unit
{
    public function testActionSeparator(): void
    {
        $event = $this->createEvent('act:data');
        $handler = new CallbackHandler();
        $handler->setActionSeparator(':');

        $handler->handle($event);

        self::assertSame('act', $event->userData['__action']);
        self::assertSame('data', $event->userData['__payload']);
    }

    public function testActionTooLong(): void
    {
        $event = $this->createEvent('abc');
        $handler = (new CallbackHandler())->setActionMaxLength(2);

        self::assertFalse($handler->handle($event));
    }

    public function testGettersAndSetters(): void
    {
        $handler = new CallbackHandler();

        self::assertSame(64, $handler->getActionMaxLength());
        self::assertNull($handler->getActionSeparator());

        self::assertSame($handler, $handler->setActionMaxLength(10));
        self::assertSame($handler, $handler->setActionSeparator(':'));

        self::assertSame(10, $handler->getActionMaxLength());
        self::assertSame(':', $handler->getActionSeparator());
    }

    public function testHandleJsonPayloadReturnsFalse(): void
    {
        $event = $this->createEvent('{"x":1}');
        $handler = (new CallbackHandler())->onAction('action1', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandleMatchingAction(): void
    {
        $event = $this->createEvent('action1');
        $handler = (new CallbackHandler())->onAction('action1', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertTrue($handler->handle($event));
        self::assertSame('action1', $event->userData['__action']);
    }

    public function testHandleNoMatch(): void
    {
        $event = $this->createEvent('other');
        $handler = (new CallbackHandler())->onAction('action1', static fn(MessageCallbackEvent $_e): bool => true);

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
