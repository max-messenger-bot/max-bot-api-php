<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Model\Response\Update;

use function str_repeat;

final class CommandHandlerTest extends Unit
{
    public function testCommandLongerThan64Characters(): void
    {
        $command = str_repeat('я', 65);
        $event = $this->createEvent('/' . $command);
        $handler = new CommandHandler();
        $handler->onCommand($command, static fn(): bool => true);

        self::assertTrue($handler->handle($event));
        self::assertSame($command, $event->userData['__command']);
    }

    public function testCommandSeparatorSplitsPayload(): void
    {
        $event = $this->createEvent('/cmd привет мир');
        $handler = new CommandHandler();
        $handler->setCommandSeparator(' ');
        $handler->onCommand('cmd', static fn(): bool => true);

        $handler->handle($event);

        self::assertSame('cmd', $event->userData['__command']);
        self::assertSame('привет мир', $event->userData['__payload']);
    }

    public function testConstructorHandlers(): void
    {
        $calls = [];
        $handler = new CommandHandler(' ', false, [
            'cmd' => [
                static function (MessageCreatedEvent $_event, string $command, ?string $payload) use (&$calls): bool {
                    $calls[] = ['named', $command, $payload];

                    return false;
                },
            ],
        ], [
            static function (MessageCreatedEvent $_event, string $command, ?string $payload) use (&$calls): void {
                $calls[] = ['remaining', $command, $payload];
            },
        ]);

        self::assertTrue($handler->handle($this->createEvent('/cmd data')));
        self::assertSame([['named', 'cmd', 'data'], ['remaining', 'cmd', 'data']], $calls);
        self::assertTrue($handler->handle($this->createEvent('/long data')));
    }

    public function testEmptyHandlerLeavesUserDataUnchanged(): void
    {
        $event = $this->createEvent('/cmd data');
        $handler = new CommandHandler(' ');

        self::assertFalse($handler->handle($event));
        self::assertArrayNotHasKey('__command', $event->userData);
        self::assertArrayNotHasKey('__payload', $event->userData);
    }

    public function testGettersAndSetters(): void
    {
        $handler = new CommandHandler();

        self::assertFalse($handler->getAllowChannels());
        self::assertNull($handler->getCommandSeparator());

        self::assertSame($handler, $handler->setAllowChannels(true));
        self::assertSame($handler, $handler->setCommandSeparator(':'));

        self::assertTrue($handler->getAllowChannels());
        self::assertSame(':', $handler->getCommandSeparator());
    }

    public function testHandleAnyChat(): void
    {
        foreach (['dialog', 'chat', 'channel'] as $chatType) {
            $handler = new CommandHandler(allowChannels: true);
            $handler->onCommand('start', static fn(): bool => true);
            self::assertTrue($handler->handle($this->createEvent('/start', $chatType)));

            $handler->setAllowChannels(false);
            self::assertSame($chatType !== 'channel', $handler->handle($this->createEvent('/start', $chatType)));
        }
    }

    public function testHandleChannelReturnsFalseByDefault(): void
    {
        $event = $this->createEvent('/start', 'channel');
        $handler = (new CommandHandler())->onCommand('start', static fn(MessageCreatedEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
        self::assertNull($event->isHandled);
        self::assertArrayNotHasKey('__command', $event->userData);
        self::assertArrayNotHasKey('__payload', $event->userData);
    }

    public function testHandleDialogAndChatByDefault(): void
    {
        foreach (['dialog', 'chat'] as $chatType) {
            $event = $this->createEvent('/start', $chatType);
            $handler = (new CommandHandler())->onCommand('start', static fn(): bool => true);

            self::assertTrue($handler->handle($event));
            self::assertSame('start', $event->userData['__command']);
        }
    }

    public function testHandleMatchingCommandWithVoidHandler(): void
    {
        $event = $this->createEvent('/start');
        $handler = (new CommandHandler())->onCommand('start', static function (MessageCreatedEvent $_e): void {});

        self::assertTrue($handler->handle($event));
        self::assertTrue($event->isHandled);
    }

    public function testHandleNoMatchingCommand(): void
    {
        $event = $this->createEvent('/other');
        $handler = (new CommandHandler())->onCommand('start', static fn(MessageCreatedEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandleNonCommandText(): void
    {
        $event = $this->createEvent('привет');
        $handler = (new CommandHandler())->onCommand('start', static fn(MessageCreatedEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandlePassesCommandAndPayloadToNamedAndFallbackHandlers(): void
    {
        $cases = ['/cmd' => null, '/cmd:' => '', '/cmd:0' => '0', '/cmd:data:more' => 'data:more'];
        foreach ($cases as $text => $expected) {
            $event = $this->createEvent($text);
            $received = [];
            $handler = new CommandHandler(commandSeparator: ':');
            $handler->onCommand('cmd', static function (
                MessageCreatedEvent $event,
                string $command,
                ?string $payload,
            ) use (&$received): bool {
                $received[] = [$event, $command, $payload];

                return false;
            });
            $handler->onCommands(static function (
                MessageCreatedEvent $event,
                string $command,
                ?string $payload,
            ) use (&$received): void {
                $received[] = [$event, $command, $payload];
            });

            self::assertTrue($handler->handle($event));
            self::assertSame([[$event, 'cmd', $expected], [$event, 'cmd', $expected]], $received);
        }
    }

    public function testMaxBotCommandWrappers(): void
    {
        $called = [];
        $bot = new MaxBot('test-token');

        self::assertSame(
            $bot,
            $bot->onCommand('start', static function (
                MessageCreatedEvent $_event,
                string $command,
                ?string $payload,
            ) use (&$called): bool {
                $called[] = [$command, $payload];

                return true;
            }),
        );
        self::assertSame($bot, $bot->onCommands(static function (
            MessageCreatedEvent $_event,
            string $command,
            ?string $payload,
        ) use (&$called): bool {
            $called[] = [$command, $payload];

            return true;
        }));

        self::assertSame($bot->getCommandHandler(), $bot->getCommandHandler());
        self::assertTrue($bot->handleEvent($this->createEvent('/start')));
        self::assertTrue($bot->handleEvent($this->createEvent('/other')));
        self::assertSame([['start', null], ['other', null]], $called);
    }

    public function testOnCommandsFallback(): void
    {
        $event = $this->createEvent('/anything');
        $handler = (new CommandHandler())->onCommands(static fn(MessageCreatedEvent $_e): bool => true);

        self::assertTrue($handler->handle($event));
    }

    public function testVoidCustomHandlerStopsList(): void
    {
        $called = [];
        $event = $this->createEvent('/start');
        $handlers = [
            static function (BaseEvent $_event) use (&$called): void {
                $called[] = 'first';
            },
            static function (BaseEvent $_event) use (&$called): void {
                $called[] = 'second';
            },
        ];

        self::assertTrue(MaxBot::handleEventUseHandlerList($event, $handlers));
        self::assertSame(['first'], $called);
        self::assertSame(HandlerListType::Custom, $event->handledIn);
    }

    public function testVoidHandlersRespectListType(): void
    {
        $called = [];
        $bot = new MaxBot('test-token');
        $bot->onPrepare(static function (BaseEvent $_event) use (&$called): void {
            $called[] = 'prepare-1';
        });
        $bot->onPrepare(static function (BaseEvent $_event) use (&$called): void {
            $called[] = 'prepare-2';
        });
        $bot->onMessageCreated(static function (MessageCreatedEvent $_event) use (&$called): void {
            $called[] = 'event-1';
        });
        $bot->onMessageCreated(static function (MessageCreatedEvent $_event) use (&$called): void {
            $called[] = 'event-2';
        });
        $bot->onFinal(static function (BaseEvent $_event) use (&$called): void {
            $called[] = 'final-1';
        });
        $bot->onFinal(static function (BaseEvent $_event) use (&$called): void {
            $called[] = 'final-2';
        });

        $event = $this->createEvent('/start');

        self::assertTrue($bot->handleEvent($event));
        self::assertSame(['prepare-1', 'prepare-2', 'event-1', 'final-1', 'final-2'], $called);
        self::assertSame(HandlerListType::Event, $event->handledIn);
    }

    public function testVoidPrepareAndFinalLeaveEventUnhandled(): void
    {
        $called = [];
        $bot = new MaxBot('test-token');
        $bot->onPrepare(static function (BaseEvent $_event) use (&$called): void {
            $called[] = 'prepare';
        });
        $bot->onFinal(static function (BaseEvent $_event) use (&$called): void {
            $called[] = 'final';
        });

        $event = $this->createEvent('/start');
        self::assertFalse($bot->handleEvent($event));
        self::assertSame(['prepare', 'final'], $called);
        self::assertNull($event->handledIn);
    }

    public function testVoidTypedAndFallbackHandlersStopTheirLists(): void
    {
        $typedCalls = [];
        $typedBot = new MaxBot('test-token');
        $typedBot->on('message_created', static function (BaseEvent $_event) use (&$typedCalls): void {
            $typedCalls[] = 'first';
        });
        $typedBot->on('message_created', static function (BaseEvent $_event) use (&$typedCalls): void {
            $typedCalls[] = 'second';
        });

        $typedEvent = $this->createEvent('/start');
        self::assertTrue($typedBot->handleEvent($typedEvent));
        self::assertSame(['first'], $typedCalls);
        self::assertSame(HandlerListType::Typed, $typedEvent->handledIn);

        $fallbackCalls = [];
        $fallbackBot = new MaxBot('test-token');
        $fallbackBot->onFallback(static function (BaseEvent $_event) use (&$fallbackCalls): void {
            $fallbackCalls[] = 'first';
        });
        $fallbackBot->onFallback(static function (BaseEvent $_event) use (&$fallbackCalls): void {
            $fallbackCalls[] = 'second';
        });

        $fallbackEvent = $this->createEvent('/start');
        self::assertTrue($fallbackBot->handleEvent($fallbackEvent));
        self::assertSame(['first'], $fallbackCalls);
        self::assertSame(HandlerListType::Fallback, $fallbackEvent->handledIn);
    }

    private function createEvent(string $text, string $chatType = 'dialog'): MessageCreatedEvent
    {
        $update = Update::newFromData([
            'update_type' => 'message_created',
            'timestamp' => 1_700_000_000_000,
            'message' => [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => $chatType, 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => $text],
            ],
        ]);

        $event = BaseEvent::new($update, new MaxApiClient('test-token'), []);
        self::assertInstanceOf(MessageCreatedEvent::class, $event);

        return $event;
    }
}
