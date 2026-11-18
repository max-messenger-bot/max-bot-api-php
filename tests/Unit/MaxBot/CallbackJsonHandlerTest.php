<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\Model\Response\Update;

use function str_repeat;

final class CallbackJsonHandlerTest extends Unit
{
    public function testActionLongerThan64Characters(): void
    {
        $action = str_repeat('a', 65);
        $event = $this->createEvent('{"cmd":"' . $action . '"}');
        $handler = new CallbackJsonHandler('cmd');
        $handler->onAction($action, static fn(): bool => true);

        self::assertTrue($handler->handle($event));
    }

    public function testActionZeroString(): void
    {
        $event = $this->createEvent('{"cmd":"0"}');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('0', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertTrue($handler->handle($event));
        self::assertSame('0', $event->userData['__action']);
    }

    public function testEmptyHandlerLeavesUserDataUnchanged(): void
    {
        $event = $this->createEvent('{"action":"act","id":7}');
        $handler = new CallbackJsonHandler();

        self::assertFalse($handler->handle($event));
        self::assertArrayNotHasKey('__action', $event->userData);
        self::assertArrayNotHasKey('__payload', $event->userData);
    }

    public function testEmptyOrNonStringActionReturnsFalse(): void
    {
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('0', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertFalse($handler->handle($this->createEvent('{"cmd":""}')));
        self::assertFalse($handler->handle($this->createEvent('{"cmd":0}')));
        self::assertFalse($handler->handle($this->createEvent('{"cmd":null}')));
    }

    public function testGettersAndSetters(): void
    {
        $handler = new CallbackJsonHandler();

        self::assertSame('action', $handler->getActionKey());

        self::assertSame($handler, $handler->setActionKey('cmd'));
        self::assertSame('cmd', $handler->getActionKey());
        $handler->onAction('act', static fn(): bool => true);
        self::assertTrue($handler->handle($this->createEvent('{"cmd":"act"}')));
        self::assertFalse($handler->handle($this->createEvent('{"action":"act"}')));
    }

    public function testHandleFallsThroughHandlers(): void
    {
        $event = $this->createEvent('{"cmd":"act","id":7}');
        $calls = [];
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static function (MessageCallbackEvent $_e) use (&$calls): bool {
                $calls[] = 'first';

                return false;
            })
            ->onAction('act', static function (MessageCallbackEvent $_e) use (&$calls): bool {
                $calls[] = 'second';

                return true;
            });

        self::assertTrue($handler->handle($event));
        self::assertSame(['first', 'second'], $calls);
        self::assertSame(['cmd' => 'act', 'id' => 7], $event->userData['__payload']);
    }

    public function testHandleMatchingAction(): void
    {
        $event = $this->createEvent('{"cmd":"act"}');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertTrue($handler->handle($event));
        self::assertSame('act', $event->userData['__action']);
    }

    public function testHandleMatchingActionWithVoidHandler(): void
    {
        $event = $this->createEvent('{"cmd":"act"}');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static function (MessageCallbackEvent $_e): void {});

        self::assertTrue($handler->handle($event));
        self::assertTrue($event->isHandled);
    }

    public function testHandleMissingKeyReturnsFalse(): void
    {
        $event = $this->createEvent('{"other":"x"}');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandleNoHandlerAcceptedReturnsFalse(): void
    {
        $event = $this->createEvent('{"cmd":"act"}');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static fn(MessageCallbackEvent $_e): bool => false);

        self::assertFalse($handler->handle($event));
        self::assertSame('act', $event->userData['__action']);
    }

    public function testHandleNonJsonReturnsFalse(): void
    {
        $event = $this->createEvent('plain');
        $handler = (new CallbackJsonHandler('cmd'))
            ->onAction('act', static fn(MessageCallbackEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandlePassesActionAndDecodedPayload(): void
    {
        $event = $this->createEvent('{"cmd":"act","data":{"id":0,"enabled":false},"optional":null}');
        $received = [];
        $handler = new CallbackJsonHandler('cmd');
        $handler->onAction('act', static function (
            MessageCallbackEvent $event,
            string $action,
            array $payload,
        ) use (&$received): void {
            $received = [$event, $action, $payload];
        });

        self::assertTrue($handler->handle($event));
        self::assertSame([
            $event,
            'act',
            ['cmd' => 'act', 'data' => ['id' => 0, 'enabled' => false], 'optional' => null],
        ], $received);
    }

    public function testHandleUnknownActionReturnsFalse(): void
    {
        $event = $this->createEvent('{"cmd":"other"}');
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
