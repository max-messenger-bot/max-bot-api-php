<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\Model\Response\Update;

use function str_repeat;

final class CallbackHandlerTest extends Unit
{
    public function testActionLongerThan64Characters(): void
    {
        $action = str_repeat('a', 65);
        $event = $this->createEvent($action);
        $handler = new CallbackHandler();
        $handler->onAction($action, static fn(): bool => true);

        self::assertTrue($handler->handle($event));
    }

    public function testActionSeparator(): void
    {
        $event = $this->createEvent('act:data');
        $handler = new CallbackHandler();
        $handler->setActionSeparator(':');
        $handler->onAction('act', static fn(): bool => true);

        $handler->handle($event);

        self::assertSame('act', $event->userData['__action']);
        self::assertSame('data', $event->userData['__payload']);
    }

    public function testConstructorHandlers(): void
    {
        $calls = [];
        $handler = new CallbackHandler(':', [
            'act' => [
                static function (MessageCallbackEvent $_event, string $action, ?string $payload) use (&$calls): void {
                    $calls[] = [$action, $payload];
                },
            ],
        ]);

        self::assertTrue($handler->handle($this->createEvent('act:data')));
        self::assertSame([['act', 'data']], $calls);
        self::assertFalse($handler->handle($this->createEvent('long:data')));
    }

    public function testGettersAndSetters(): void
    {
        $handler = new CallbackHandler();

        self::assertNull($handler->getActionSeparator());

        self::assertSame($handler, $handler->setActionSeparator(':'));

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

    public function testHandleMatchingActionWithVoidHandler(): void
    {
        $event = $this->createEvent('action1');
        $handler = (new CallbackHandler())->onAction('action1', static function (MessageCallbackEvent $_e): void {});

        self::assertTrue($handler->handle($event));
        self::assertTrue($event->isHandled);
    }

    public function testHandleNoHandlers(): void
    {
        $event = $this->createEvent('action1');
        $handler = new CallbackHandler();

        self::assertFalse($handler->handle($event));
        self::assertArrayNotHasKey('__action', $event->userData);
        self::assertArrayNotHasKey('__payload', $event->userData);
    }

    public function testHandleNoMatch(): void
    {
        $event = $this->createEvent('other');
        $handler = (new CallbackHandler())->onAction('action1', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandlePassesActionAndPayload(): void
    {
        foreach (['act' => null, 'act:' => '', 'act:0' => '0', 'act:data:more' => 'data:more'] as $input => $expected) {
            $event = $this->createEvent($input);
            $received = [];
            $handler = new CallbackHandler(actionSeparator: ':');
            $handler->onAction('act', static function (
                MessageCallbackEvent $event,
                string $action,
                ?string $payload,
            ) use (&$received): void {
                $received = [$event, $action, $payload];
            });

            self::assertTrue($handler->handle($event));
            self::assertSame([$event, 'act', $expected], $received);
        }
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
