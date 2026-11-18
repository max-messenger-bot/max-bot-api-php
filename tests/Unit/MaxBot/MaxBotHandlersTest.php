<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use ArrayObject;
use Closure;
use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\Exception\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStoppedEvent;
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogClearedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogMutedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogUnmutedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\UnknownEvent;
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\UserRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;
use RuntimeException;
use Throwable;

use function str_repeat;

/**
 * Регистрация обработчиков бота и разбор событий по ним.
 */
final class MaxBotHandlersTest extends Unit
{
    /**
     * @return iterable<string, array{Closure(MaxBot, Closure): MaxBot, array<string, mixed>, class-string<BaseEvent>}>
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public static function eventRegistrars(): iterable
    {
        $user = ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false];

        yield 'onBotAddedToChat' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onBotAddedToChat($handler),
            ['update_type' => 'bot_added', 'chat_id' => 100, 'user' => $user, 'is_channel' => false],
            BotAddedToChatEvent::class,
        ];
        yield 'onBotAdminPermissionsChanged' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onBotAdminPermissionsChanged($handler),
            [
                'update_type' => 'bot_admin_permissions_changed',
                'chat_id' => 100,
                'user_id' => 200,
                'bot_id' => 789,
                'is_channel' => false,
                'is_admin' => true,
            ],
            BotAdminPermissionsChangedEvent::class,
        ];
        yield 'onBotRemovedFromChat' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onBotRemovedFromChat($handler),
            ['update_type' => 'bot_removed', 'chat_id' => 100, 'user' => $user, 'is_channel' => false],
            BotRemovedFromChatEvent::class,
        ];
        yield 'onBotStarted' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onBotStarted($handler),
            ['update_type' => 'bot_started', 'chat_id' => 100, 'user' => $user],
            BotStartedEvent::class,
        ];
        yield 'onBotStopped' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onBotStopped($handler),
            ['update_type' => 'bot_stopped', 'chat_id' => 100, 'user' => $user],
            BotStoppedEvent::class,
        ];
        yield 'onChatTitleChanged' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onChatTitleChanged($handler),
            ['update_type' => 'chat_title_changed', 'chat_id' => 100, 'user' => $user, 'title' => 'Название'],
            ChatTitleChangedEvent::class,
        ];
        yield 'onCommentCreated' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onCommentCreated($handler),
            ['update_type' => 'comment_created', 'message' => FakeMaxHttpClient::commentData()],
            CommentCreatedEvent::class,
        ];
        yield 'onCommentEdited' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onCommentEdited($handler),
            ['update_type' => 'comment_edited', 'message' => FakeMaxHttpClient::commentData()],
            CommentEditedEvent::class,
        ];
        yield 'onCommentRemoved' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onCommentRemoved($handler),
            [
                'update_type' => 'comment_removed',
                'message_id' => 'mid.comment',
                'chat_id' => 100,
                'user_id' => 200,
                'post_id' => 'mid.post',
            ],
            CommentRemovedEvent::class,
        ];
        yield 'onDialogCleared' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onDialogCleared($handler),
            ['update_type' => 'dialog_cleared', 'chat_id' => 100, 'user' => $user],
            DialogClearedEvent::class,
        ];
        yield 'onDialogMuted' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onDialogMuted($handler),
            ['update_type' => 'dialog_muted', 'chat_id' => 100, 'user' => $user, 'muted_until' => 1_700_000_500],
            DialogMutedEvent::class,
        ];
        yield 'onDialogRemoved' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onDialogRemoved($handler),
            ['update_type' => 'dialog_removed', 'chat_id' => 100, 'user' => $user],
            DialogRemovedEvent::class,
        ];
        yield 'onDialogUnmuted' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onDialogUnmuted($handler),
            ['update_type' => 'dialog_unmuted', 'chat_id' => 100, 'user' => $user],
            DialogUnmutedEvent::class,
        ];
        yield 'onMessageCallback' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onMessageCallback($handler),
            [
                'update_type' => 'message_callback',
                'callback' => self::callbackData('like'),
                'message' => self::messageData(),
            ],
            MessageCallbackEvent::class,
        ];
        yield 'onMessageCreated' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onMessageCreated($handler),
            ['update_type' => 'message_created', 'message' => self::messageData()],
            MessageCreatedEvent::class,
        ];
        yield 'onMessageEdited' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onMessageEdited($handler),
            ['update_type' => 'message_edited', 'message' => self::messageData()],
            MessageEditedEvent::class,
        ];
        yield 'onMessageRemoved' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onMessageRemoved($handler),
            ['update_type' => 'message_removed', 'message_id' => 'mid.1', 'chat_id' => 100, 'user_id' => 200],
            MessageRemovedEvent::class,
        ];
        yield 'onUnknown' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onUnknown($handler),
            ['update_type' => 'something_unknown'],
            UnknownEvent::class,
        ];
        yield 'onUserAddedToChat' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onUserAddedToChat($handler),
            ['update_type' => 'user_added', 'chat_id' => 100, 'user' => $user, 'is_channel' => false],
            UserAddedToChatEvent::class,
        ];
        yield 'onUserRemovedFromChat' => [
            static fn(MaxBot $bot, Closure $handler): MaxBot => $bot->onUserRemovedFromChat($handler),
            ['update_type' => 'user_removed', 'chat_id' => 100, 'user' => $user, 'is_channel' => false],
            UserRemovedFromChatEvent::class,
        ];
    }

    public function testCallbackHandlerRegistration(): void
    {
        $bot = $this->createBot();
        $payload = null;
        $handler = new CallbackHandler(':');
        $bot->onMessageCallback($handler->handle(...));
        $handler->onAction('like', static function (MessageCallbackEvent $event) use (&$payload): void {
            /** @var mixed $payload */
            $payload = $event->userData['__payload'];
        });

        self::assertSame(':', $handler->actionSeparator);
        self::assertTrue($bot->handleUpdate($this->callbackUpdate('like:5')));
        self::assertSame('5', $payload);
        self::assertFalse($bot->handleUpdate($this->callbackUpdate('dislike:5')));
    }

    public function testCallbackJsonHandlerRegistration(): void
    {
        $bot = $this->createBot();
        $payload = null;
        $handler = new CallbackJsonHandler('cmd');
        $bot->onMessageCallback($handler->handle(...));
        $handler->onAction('like', static function (MessageCallbackEvent $event) use (&$payload): void {
            /** @var mixed $payload */
            $payload = $event->userData['__payload'];
        });

        self::assertSame('cmd', $handler->getActionKey());
        self::assertTrue($bot->handleUpdate($this->callbackUpdate('{"cmd":"like","id":5}')));
        self::assertSame(['cmd' => 'like', 'id' => 5], $payload);
        self::assertFalse($bot->handleUpdate($this->callbackUpdate('{"cmd":"dislike"}')));
    }

    public function testCommandHandlerRegistration(): void
    {
        $bot = $this->createBot();
        $first = new CommandHandler(':');
        $second = new CommandHandler(' ');
        $bot->onMessageCreated($first->handle(...), prepend: true);
        $bot->onMessageCreated($second->handle(...), prepend: true);
        $calls = [];
        $first->onCommand('first', static function () use (&$calls): void {
            $calls[] = 'first';
        });
        $second->onCommand('second', static function () use (&$calls): void {
            $calls[] = 'second';
        });

        self::assertNotSame($first, $second);
        self::assertNotSame($first, $bot->getCommandHandler());
        self::assertNotSame($second, $bot->getCommandHandler());
        self::assertSame(':', $first->getCommandSeparator());
        self::assertTrue($bot->handleUpdate($this->messageUpdate('/first:data')));
        self::assertTrue($bot->handleUpdate($this->messageUpdate('/second data')));
        self::assertSame(['first', 'second'], $calls);
    }

    /**
     * @param Closure(MaxBot, Closure): MaxBot $register
     * @param array<string, mixed> $data
     * @param class-string<BaseEvent> $eventClass
     * @dataProvider eventRegistrars
     */
    public function testEventRegistrar(Closure $register, array $data, string $eventClass): void
    {
        $bot = $this->createBot();
        $events = [];

        $result = $register($bot, static function (BaseEvent $event) use (&$events): void {
            $events[] = $event;
        });
        $handled = $bot->handleUpdate(Update::newFromData($data + ['timestamp' => 1_700_000_000_000]));

        self::assertSame($bot, $result);
        self::assertTrue($handled);
        self::assertCount(1, $events);
        $event = $events[0] ?? null;
        self::assertInstanceOf($eventClass, $event);
        self::assertSame(HandlerListType::Event, $event->handledIn);
    }

    /**
     * @param Closure(MaxBot, Closure): MaxBot $register
     * @param array<string, mixed> $data
     * @dataProvider eventRegistrars
     */
    public function testEventRegistrarIgnoresOtherEvents(Closure $register, array $data): void
    {
        $bot = $this->createBot();
        $called = false;
        $register($bot, static function (BaseEvent $_event) use (&$called): void {
            $called = true;
        });
        $otherType = $data['update_type'] === 'bot_started' ? 'bot_stopped' : 'bot_started';
        $other = ['update_type' => $otherType, 'chat_id' => 100, 'user' => ['user_id' => 200, 'first_name' => 'Иван']];

        self::assertFalse($bot->handleUpdate(Update::newFromData($other + ['timestamp' => 1_700_000_000_000])));
        self::assertFalse($called);
    }

    public function testGetCallbackHandlerReusesInstanceWithoutDuplicateRegistration(): void
    {
        $bot = $this->createBot();
        $first = $bot->getCallbackHandler()->setActionSeparator(':');
        self::assertSame($first, $bot->getCallbackHandler());
        self::assertSame(':', $first->actionSeparator);
        $calls = 0;
        $first->onAction('like', static function () use (&$calls): bool {
            ++$calls;

            return false;
        });
        self::assertFalse($bot->handleUpdate($this->callbackUpdate('like:5')));
        self::assertSame(1, $calls);

        $second = new CallbackHandler();
        $bot->onMessageCallback($second->handle(...));
        self::assertNotSame($first, $second);
        self::assertSame($first, $bot->getCallbackHandler());
    }

    public function testGetCallbackJsonHandlerReusesInstanceWithoutDuplicateRegistration(): void
    {
        $bot = $this->createBot();
        $first = $bot->getCallbackJsonHandler()->setActionKey('cmd');
        self::assertSame($first, $bot->getCallbackJsonHandler());
        self::assertSame('cmd', $first->getActionKey());
        $calls = 0;
        $first->onAction('like', static function () use (&$calls): bool {
            ++$calls;

            return false;
        });
        self::assertFalse($bot->handleUpdate($this->callbackUpdate('{"cmd":"like"}')));
        self::assertSame(1, $calls);

        $second = new CallbackJsonHandler('action');
        $bot->onMessageCallback($second->handle(...));
        self::assertNotSame($first, $second);
        self::assertSame($first, $bot->getCallbackJsonHandler());
    }

    public function testGetCommandHandlerDoesNotRegisterTwice(): void
    {
        $bot = $this->createBot();
        $handler = $bot->getCommandHandler()->setCommandSeparator(':');
        self::assertSame($handler, $bot->getCommandHandler());
        $calls = 0;
        $handler->onCommand('start', static function () use (&$calls): bool {
            ++$calls;

            return false;
        });

        self::assertFalse($bot->handleUpdate($this->messageUpdate('/start:data')));
        self::assertSame(1, $calls);
        $second = new CommandHandler(' ');
        $bot->onMessageCreated($second->handle(...), prepend: true);
        self::assertNotSame($handler, $second);
        self::assertSame($handler, $bot->getCommandHandler());
    }

    public function testGetCommandHandlerRunsBeforeMessageHandlers(): void
    {
        $bot = $this->createBot();
        /** @var ArrayObject<int, string> $calls */
        $calls = new ArrayObject();
        $bot->onMessageCreated(static function (MessageCreatedEvent $_event) use ($calls): void {
            $calls->append('message');
        });
        $bot->onCommand('start', static function (MessageCreatedEvent $_event) use ($calls): void {
            $calls->append('command');
        });

        self::assertTrue($bot->handleUpdate($this->messageUpdate('/start')));
        self::assertTrue($bot->handleUpdate($this->messageUpdate('Привет')));
        self::assertSame(['command', 'message'], $calls->getArrayCopy());
    }

    public function testGetCommandHandlerUsesDefaultSeparator(): void
    {
        $handler = $this->createBot()->getCommandHandler();

        self::assertNull($handler->getCommandSeparator());
    }

    public function testHandleEventWithPrepareHandler(): void
    {
        $bot = $this->createBot();
        $calls = [];
        $bot->onPrepare(static function (BaseEvent $_event) use (&$calls): bool {
            $calls[] = 'prepare';

            return true;
        });
        $bot->onMessageCreated(static function (BaseEvent $_event) use (&$calls): void {
            $calls[] = 'message';
        });
        $bot->onFinal(static function (BaseEvent $_event) use (&$calls): void {
            $calls[] = 'final';
        });
        $event = $bot->makeEvent($this->messageUpdate('Привет'));

        self::assertTrue($bot->handleEvent($event));
        self::assertSame(['prepare', 'final'], $calls);
        self::assertSame(HandlerListType::Prepare, $event->handledIn);
    }

    public function testHandleEventWithoutHandlers(): void
    {
        $bot = $this->createBot();
        $event = $bot->makeEvent($this->messageUpdate('Привет'));

        self::assertFalse($bot->handleEvent($event));
        self::assertNull($event->handledIn);
    }

    public function testHandleFromServer(): void
    {
        $http = new FakeMaxHttpClient([
            'updates' => [
                [
                    'update_type' => 'message_created',
                    'timestamp' => 1_700_000_000_000,
                    'message' => self::messageData(),
                ],
                ['update_type' => 'something_unknown', 'timestamp' => 1_700_000_000_001],
            ],
            'marker' => 42,
        ]);
        $bot = $this->createBot($http);
        $events = [];
        $bot->onFallback(static function (BaseEvent $event) use (&$events): void {
            $events[] = $event::class;
        });

        $marker = $bot->handleFromServer(10, 30, 7, [UpdateType::MessageCreated, 'something_unknown']);

        self::assertSame(42, $marker);
        self::assertSame([MessageCreatedEvent::class, UnknownEvent::class], $events);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('getUpdates', $call['method']);
        self::assertSame('/updates', $call['path']);
        self::assertSame(
            ['limit' => 10, 'timeout' => 30, 'marker' => 7, 'types' => 'message_created,something_unknown'],
            $call['query'],
        );
        self::assertSame(40_000, $http->updatesTimeout);
    }

    public function testHandleFromServerWithDefaults(): void
    {
        $http = new FakeMaxHttpClient(['updates' => []]);

        self::assertNull($this->createBot($http)->handleFromServer());

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['limit' => 1, 'timeout' => 60], $call['query']);
    }

    public function testHandlerListRestoresTypeAfterException(): void
    {
        $event = $this->createBot()->makeEvent($this->messageUpdate('Привет'));
        $event->currentHandlerListType = HandlerListType::Prepare;
        $exception = new RuntimeException('Сбой обработчика');

        try {
            MaxBot::handleEventUseHandlerList(
                $event,
                [static function (BaseEvent $event) use ($exception): never {
                    self::assertSame(HandlerListType::Event, $event->currentHandlerListType);

                    throw $exception;
                }],
                HandlerListType::Event,
            );
            self::fail('Ожидалось исключение обработчика');
        } catch (RuntimeException $e) {
            self::assertSame($exception, $e);
        }

        self::assertSame(HandlerListType::Prepare, $event->currentHandlerListType);
        self::assertNull($event->handledIn);
    }

    public function testMakeUpdateFromString(): void
    {
        $update = MaxBot::makeUpdateFromString('{"update_type":"bot_started","timestamp":1700000000000,'
            . '"chat_id":100,"user":{"user_id":200,"first_name":"Иван"}}');

        self::assertSame('bot_started', $update->getUpdateTypeRaw());
    }

    public function testMakeUpdateFromStringWithInvalidJson(): void
    {
        try {
            MaxBot::makeUpdateFromString('{');
            self::fail('Ожидалась ошибка разбора JSON');
        } catch (BadRequestException $e) {
            self::assertInstanceOf(JsonException::class, $e->getPrevious());
        }
    }

    public function testMakeUpdateFromStringWithScalar(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Data is not array.');

        MaxBot::makeUpdateFromString('"update"');
    }

    public function testMakeUpdateFromStringWithTooDeepJson(): void
    {
        $this->expectException(BadRequestException::class);

        MaxBot::makeUpdateFromString(str_repeat('[', 17) . str_repeat(']', 17));
    }

    public function testOnCallbackJsonUsesCustomActionKey(): void
    {
        $bot = $this->createBot();
        $bot->getCallbackJsonHandler()->setActionKey('cmd');
        $payload = null;
        $result = $bot->onCallbackJson('like', static function (
            MessageCallbackEvent $_event,
            string $action,
            array $data,
        ) use (&$payload): void {
            $payload = [$action, $data];
        });

        self::assertSame($bot, $result);
        self::assertSame('cmd', $bot->getCallbackJsonHandler()->getActionKey());
        self::assertTrue($bot->handleUpdate($this->callbackUpdate('{"cmd":"like","id":5}')));
        self::assertSame(['like', ['cmd' => 'like', 'id' => 5]], $payload);
    }

    public function testOnCallbacksUseConfiguredHandlersAndStayIndependent(): void
    {
        $bot = $this->createBot();
        $bot->getCallbackHandler()->setActionSeparator(':');
        $calls = [];
        $textResult = $bot->onCallback('like', static function (
            MessageCallbackEvent $_event,
            string $action,
            ?string $payload,
        ) use (&$calls): void {
            $calls[] = [$action, $payload];
        });
        $jsonResult = $bot->onCallbackJson('like', static function (
            MessageCallbackEvent $_event,
            string $action,
            array $payload,
        ) use (&$calls): void {
            $calls[] = [$action, $payload];
        });

        self::assertSame($bot, $textResult);
        self::assertSame($bot, $jsonResult);
        self::assertTrue($bot->handleUpdate($this->callbackUpdate('like:5')));
        self::assertTrue($bot->handleUpdate($this->callbackUpdate('{"action":"like","id":6}')));
        self::assertSame([['like', '5'], ['like', ['action' => 'like', 'id' => 6]]], $calls);
    }

    public function testOnException(): void
    {
        $bot = $this->createBot();
        $exception = new RuntimeException('Сбой обработчика');
        $caught = null;

        $result = $bot->onException(static function (Throwable $e) use (&$caught): bool {
            $caught = $e;

            return true;
        });
        $bot->onMessageCreated(static function (MessageCreatedEvent $_event) use ($exception): never {
            throw $exception;
        });

        self::assertSame($bot, $result);
        self::assertTrue($bot->handleUpdate($this->messageUpdate('Привет')));
        self::assertSame($exception, $caught);
    }

    public function testOnExceptionNotRegistered(): void
    {
        $bot = $this->createBot();
        $exception = new RuntimeException('Сбой обработчика');
        $bot->onMessageCreated(static function (MessageCreatedEvent $_event) use ($exception): never {
            throw $exception;
        });

        try {
            $bot->handleUpdate($this->messageUpdate('Привет'));
            self::fail('Ожидалось исключение обработчика');
        } catch (RuntimeException $e) {
            self::assertSame($exception, $e);
        }
    }

    public function testOnMessageCreatedPrependOrder(): void
    {
        $bot = $this->createBot();
        $calls = [];
        $makeHandler = static function (string $name) use (&$calls): Closure {
            return static function () use (&$calls, $name): bool {
                $calls[] = $name;

                return false;
            };
        };
        self::assertSame($bot, $bot->onMessageCreated($makeHandler('first'), prepend: true));
        $bot->onMessageCreated($makeHandler('last'));
        $bot->onMessageCreated($makeHandler('second'), prepend: true);

        self::assertFalse($bot->handleUpdate($this->messageUpdate('message')));
        self::assertSame(['second', 'first', 'last'], $calls);
    }

    /**
     * @return array<string, mixed>
     */
    private static function callbackData(string $payload): array
    {
        return [
            'timestamp' => 1_700_000_000_000,
            'callback_id' => 'cb.1',
            'payload' => $payload,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ];
    }

    private function callbackUpdate(string $payload): Update
    {
        return Update::newFromData([
            'update_type' => 'message_callback',
            'timestamp' => 1_700_000_000_000,
            'callback' => self::callbackData($payload),
            'message' => self::messageData(),
        ]);
    }

    private function createBot(?FakeMaxHttpClient $http = null): MaxBot
    {
        return new MaxBot(new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http ?? new FakeMaxHttpClient()));
    }

    /**
     * @return array<string, mixed>
     */
    private static function messageData(string $text = 'Привет'): array
    {
        return [
            'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => $text],
        ];
    }

    private function messageUpdate(string $text): Update
    {
        return Update::newFromData([
            'update_type' => 'message_created',
            'timestamp' => 1_700_000_000_000,
            'message' => self::messageData($text),
        ]);
    }
}
