<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Attribute\On;
use MaxMessenger\Bot\MaxBot\Attribute\OnBotAddedToChat;
use MaxMessenger\Bot\MaxBot\Attribute\OnBotAdminPermissionsChanged;
use MaxMessenger\Bot\MaxBot\Attribute\OnBotRemovedFromChat;
use MaxMessenger\Bot\MaxBot\Attribute\OnBotStarted;
use MaxMessenger\Bot\MaxBot\Attribute\OnBotStopped;
use MaxMessenger\Bot\MaxBot\Attribute\OnCallback;
use MaxMessenger\Bot\MaxBot\Attribute\OnCallbackJson;
use MaxMessenger\Bot\MaxBot\Attribute\OnChatTitleChanged;
use MaxMessenger\Bot\MaxBot\Attribute\OnCommand;
use MaxMessenger\Bot\MaxBot\Attribute\OnCommands;
use MaxMessenger\Bot\MaxBot\Attribute\OnCommentCreated;
use MaxMessenger\Bot\MaxBot\Attribute\OnCommentEdited;
use MaxMessenger\Bot\MaxBot\Attribute\OnCommentRemoved;
use MaxMessenger\Bot\MaxBot\Attribute\OnDialogCleared;
use MaxMessenger\Bot\MaxBot\Attribute\OnDialogMuted;
use MaxMessenger\Bot\MaxBot\Attribute\OnDialogRemoved;
use MaxMessenger\Bot\MaxBot\Attribute\OnDialogUnmuted;
use MaxMessenger\Bot\MaxBot\Attribute\OnException;
use MaxMessenger\Bot\MaxBot\Attribute\OnFallback;
use MaxMessenger\Bot\MaxBot\Attribute\OnFinal;
use MaxMessenger\Bot\MaxBot\Attribute\OnMessageCallback;
use MaxMessenger\Bot\MaxBot\Attribute\OnMessageCreated;
use MaxMessenger\Bot\MaxBot\Attribute\OnMessageEdited;
use MaxMessenger\Bot\MaxBot\Attribute\OnMessageRemoved;
use MaxMessenger\Bot\MaxBot\Attribute\OnPrepare;
use MaxMessenger\Bot\MaxBot\Attribute\OnUnknown;
use MaxMessenger\Bot\MaxBot\Attribute\OnUserAddedToChat;
use MaxMessenger\Bot\MaxBot\Attribute\OnUserRemovedFromChat;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\HandlerListType;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;
use ReturnTypeWillChange;
use RuntimeException;
use Throwable;

/**
 * Регистрация и вызов методов объекта через атрибуты обработчиков.
 */
final class AttributeHandlersTest extends Unit
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function eventRegistrars(): iterable
    {
        foreach (UpdateType::cases() as $type) {
            yield $type->value => [$type->value];
        }
        yield 'unknown' => ['something_unknown'];
    }

    public function testCommandsAndCallbacksReceiveArguments(): void
    {
        $handlers = new class {
            /**
             * @var list<array{string, mixed}>
             */
            public array $calls = [];

            #[OnCallback('like')]
            public function callback(MessageCallbackEvent $_event, string $action, ?string $payload): void
            {
                $this->calls[] = [$action, $payload];
            }

            #[OnCallbackJson('save')]
            public function callbackJson(MessageCallbackEvent $_event, string $action, array $payload): void
            {
                $this->calls[] = [$action, $payload];
            }

            #[OnCommand('start')]
            #[OnCommand('help')]
            public function command(MessageCreatedEvent $_event, string $command, ?string $payload): void
            {
                $this->calls[] = [$command, $payload];
            }

            #[OnCommands]
            public function commands(MessageCreatedEvent $_event, string $command, ?string $payload): void
            {
                $this->calls[] = [$command, $payload];
            }
        };
        $bot = new MaxBot('test-token');
        $bot->getCommandHandler()->setCommandSeparator(' ');
        $bot->getCallbackJsonHandler()->setActionKey('cmd');
        $bot->registerHandlers($handlers);

        foreach (['/start one', '/help two', '/other three'] as $text) {
            self::assertTrue($bot->handleUpdate($this->messageUpdate($text)));
        }
        foreach (['like', '{"cmd":"save","id":5}'] as $payload) {
            self::assertTrue($bot->handleUpdate(Update::newFromData([
                'update_type' => 'message_callback',
                'timestamp' => 1_700_000_000_000,
                'callback' => [
                    'timestamp' => 1_700_000_000_000,
                    'callback_id' => 'callback.1',
                    'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                    'payload' => $payload,
                ],
                'message' => FakeMaxHttpClient::messageData(),
            ])));
        }

        self::assertSame([
            ['start', 'one'],
            ['help', 'two'],
            ['other', 'three'],
            ['like', null],
            ['save', ['cmd' => 'save', 'id' => 5]],
        ], $handlers->calls);
    }

    /**
     * @dataProvider eventRegistrars
     */
    public function testEventAttributes(string $type): void
    {
        $handlers = new class {
            /**
             * Событие, переданное обработчику.
             */
            public ?BaseEvent $event = null;

            #[OnBotAddedToChat]
            #[OnBotAdminPermissionsChanged]
            #[OnBotRemovedFromChat]
            #[OnBotStarted]
            #[OnBotStopped]
            #[OnChatTitleChanged]
            #[OnCommentCreated]
            #[OnCommentEdited]
            #[OnCommentRemoved]
            #[OnDialogCleared]
            #[OnDialogMuted]
            #[OnDialogRemoved]
            #[OnDialogUnmuted]
            #[OnMessageCallback]
            #[OnMessageCreated]
            #[OnMessageEdited]
            #[OnMessageRemoved]
            #[OnUnknown]
            #[OnUserAddedToChat]
            #[OnUserRemovedFromChat]
            public function handle(BaseEvent $event): void
            {
                $this->event = $event;
            }
        };
        $bot = new MaxBot('test-token');
        $user = ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false];
        $update = Update::newFromData([
            'update_type' => $type,
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user_id' => 200,
            'bot_id' => 789,
            'user' => $user,
            'is_channel' => false,
            'is_admin' => true,
            'title' => 'Название',
            'muted_until' => 1_700_000_500,
            'message_id' => 'mid.1',
            'post_id' => 'mid.post',
            'message' => $type === 'comment_created' || $type === 'comment_edited'
                ? FakeMaxHttpClient::commentData()
                : FakeMaxHttpClient::messageData(),
            'callback' => [
                'timestamp' => 1_700_000_000_000,
                'callback_id' => 'callback.1',
                'user' => $user,
                'payload' => 'like',
            ],
        ]);

        self::assertSame($bot, $bot->registerHandlers($handlers));
        self::assertTrue($bot->handleUpdate($update));
        self::assertInstanceOf(BaseEvent::class, $handlers->event);
        self::assertSame($update, $handlers->event->update);
        self::assertSame(HandlerListType::Event, $handlers->event->handledIn);
    }

    public function testExceptionAttribute(): void
    {
        $handlers = new class {
            /**
             * Исключение из обработчика.
             */
            public ?Throwable $exception = null;
            /**
             * Событие, вызвавшее исключение.
             */
            public ?BaseEvent $event = null;

            #[OnException]
            public function exception(Throwable $exception, BaseEvent $event): bool
            {
                $this->exception = $exception;
                $this->event = $event;

                return true;
            }

            #[OnMessageCreated]
            public function message(MessageCreatedEvent $_event): void
            {
                throw new RuntimeException('handler failure');
            }
        };
        $bot = (new MaxBot('test-token'))->registerHandlers($handlers);

        self::assertTrue($bot->handleUpdate($this->messageUpdate('test')));
        self::assertInstanceOf(RuntimeException::class, $handlers->exception);
        self::assertSame('handler failure', $handlers->exception->getMessage());
        self::assertInstanceOf(MessageCreatedEvent::class, $handlers->event);
    }

    public function testInheritedMethodsAndUnrelatedAttributes(): void
    {
        $handlers = new class extends AttributeHandlersParentFixture {
            #[OnMessageCreated]
            protected function protectedHandler(): void
            {
                throw new RuntimeException('Protected method must not be registered.');
            }

            #[OnMessageCreated]
            private function privateHandler(): void
            {
                throw new RuntimeException('Private method must not be registered.');
            }
        };
        $bot = (new MaxBot('test-token'))->registerHandlers($handlers);

        self::assertTrue($bot->handleUpdate($this->messageUpdate('test')));
        self::assertSame(1, $handlers->calls);
        self::assertSame($bot, $bot->registerHandlers(new class {
            #[ReturnTypeWillChange]
            public function unrelated(): void {}
        }));
    }

    public function testPrepareTypedFallbackAndFinalAttributes(): void
    {
        $handlers = new class {
            /**
             * @var list<string>
             */
            public array $calls = [];

            #[OnFallback]
            public function fallback(BaseEvent $_event): void
            {
                $this->calls[] = 'fallback';
            }

            #[OnFinal]
            public function final(BaseEvent $_event): void
            {
                $this->calls[] = 'final';
            }

            #[OnPrepare]
            public function prepare(BaseEvent $_event): void
            {
                $this->calls[] = 'prepare';
            }

            #[On(UpdateType::MessageCreated)]
            #[On('message_edited')]
            public function typed(BaseEvent $_event): bool
            {
                $this->calls[] = 'typed';

                return false;
            }
        };
        $bot = (new MaxBot('test-token'))->registerHandlers($handlers);

        foreach (['message_created', 'message_edited'] as $type) {
            self::assertTrue($bot->handleUpdate(Update::newFromData([
                'update_type' => $type,
                'timestamp' => 1_700_000_000_000,
                'message' => FakeMaxHttpClient::messageData(),
            ])));
        }
        self::assertSame([
            'prepare',
            'typed',
            'fallback',
            'final',
            'prepare',
            'typed',
            'fallback',
            'final',
        ], $handlers->calls);
    }

    private function messageUpdate(string $text): Update
    {
        return Update::newFromData([
            'update_type' => 'message_created',
            'timestamp' => 1_700_000_000_000,
            'message' => [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => $text],
            ],
        ]);
    }
}

/**
 * Родительский класс с обработчиком для проверки наследования.
 *
 * @psalm-consistent-constructor
 */
class AttributeHandlersParentFixture
{
    /**
     * Число вызовов унаследованного обработчика.
     */
    public int $calls = 0;

    /**
     * @api
     */
    #[OnMessageCreated]
    public function message(MessageCreatedEvent $_event): void
    {
        $this->calls++;
    }
}
