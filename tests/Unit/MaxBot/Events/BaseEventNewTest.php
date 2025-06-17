<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Events;

use ArrayObject;
use Closure;
use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Events\BaseEvent;
use MaxMessenger\Bot\MaxBot\Events\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Events\BotRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\Events\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Events\BotStoppedEvent;
use MaxMessenger\Bot\MaxBot\Events\ChatTitleChangedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogClearedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogMutedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogRemovedEvent;
use MaxMessenger\Bot\MaxBot\Events\DialogUnmutedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageRemovedEvent;
use MaxMessenger\Bot\MaxBot\Events\UnknownEvent;
use MaxMessenger\Bot\MaxBot\Events\UserAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Events\UserRemovedFromChatEvent;
use MaxMessenger\Bot\Models\Responses\Update;
use Throwable;

final class BaseEventNewTest extends Unit
{
    private MaxApiClient $apiClient;
    /** @var list<Closure(Throwable, BaseEvent): void> */
    private array $exceptionHandlers = [];
    private ArrayObject $userData;

    /**
     * @param class-string<BaseEvent> $expectedEventClass
     * @dataProvider updateClassMappingProvider
     */
    public function testNewCreatesCorrectEventClass(Update $update, string $expectedEventClass): void
    {
        $userData = new ArrayObject();
        $event = BaseEvent::new($update, $this->apiClient, $this->exceptionHandlers, $userData);

        self::assertInstanceOf($expectedEventClass, $event);
        self::assertInstanceOf(BaseEvent::class, $event);
    }

    public function testNewPassesCorrectParameters(): void
    {
        $timestamp = time() * 1000;
        $update = Update::newFromData([
            'update_type' => 'message_created',
            'timestamp' => $timestamp,
            'message' => [
                'mid' => 'mid.test',
                'chat_id' => 123,
                'body' => ['text' => 'test'],
                'timestamp' => $timestamp
            ],
        ]);
        $userData = new ArrayObject(['key' => 'value']);
        $exceptionHandlers = [
            static function (Throwable $_e, BaseEvent $_event): void {
            }
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers, $userData);

        self::assertSame($update, $event->update);
        self::assertSame($this->apiClient, $event->apiClient);
        self::assertSame($userData, $event->userData);
    }

    public function testNewReturnsUnknownEventForUnknownUpdateType(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $userData = new ArrayObject();

        $event = BaseEvent::new($update, $this->apiClient, $this->exceptionHandlers, $userData);

        self::assertInstanceOf(UnknownEvent::class, $event);
    }

    /**
     * @return array<string, array{0: Update, 1: class-string}>
     */
    public static function updateClassMappingProvider(): array
    {
        $timestamp = time() * 1000;
        $messageData = ['mid' => 'mid.test', 'chat_id' => 123, 'body' => ['text' => 'test'], 'timestamp' => $timestamp];
        $userData = ['user_id' => 123, 'name' => 'Test User'];

        return [
            'BotAddedToChatUpdate' => [
                Update::newFromData(
                    ['update_type' => 'bot_added', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData]
                ),
                BotAddedToChatEvent::class
            ],
            'BotRemovedFromChatUpdate' => [
                Update::newFromData(
                    ['update_type' => 'bot_removed', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData]
                ),
                BotRemovedFromChatEvent::class
            ],
            'BotStartedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'bot_started', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData]
                ),
                BotStartedEvent::class
            ],
            'BotStoppedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'bot_stopped', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData]
                ),
                BotStoppedEvent::class
            ],
            'ChatTitleChangedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'chat_title_changed',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'title' => 'New Title',
                        'user' => $userData
                    ]
                ),
                ChatTitleChangedEvent::class
            ],
            'DialogClearedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_cleared',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'user' => $userData
                    ]
                ),
                DialogClearedEvent::class
            ],
            'DialogMutedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_muted',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'muted_until' => $timestamp,
                        'user' => $userData
                    ]
                ),
                DialogMutedEvent::class
            ],
            'DialogRemovedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_removed',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'user' => $userData
                    ]
                ),
                DialogRemovedEvent::class
            ],
            'DialogUnmutedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_unmuted',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'user' => $userData
                    ]
                ),
                DialogUnmutedEvent::class
            ],
            'MessageCallbackUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'message_callback',
                        'timestamp' => $timestamp,
                        'message' => $messageData,
                        'callback' => ['callback_id' => 'test', 'payload' => 'test', 'user' => $userData]
                    ]
                ),
                MessageCallbackEvent::class
            ],
            'MessageCreatedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'message_created', 'timestamp' => $timestamp, 'message' => $messageData]
                ),
                MessageCreatedEvent::class
            ],
            'MessageEditedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'message_edited', 'timestamp' => $timestamp, 'message' => $messageData]
                ),
                MessageEditedEvent::class
            ],
            'MessageRemovedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'message_removed',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'message_id' => 'mid.test',
                        'user_id' => 123
                    ]
                ),
                MessageRemovedEvent::class
            ],
            'UserAddedToChatUpdate' => [
                Update::newFromData(
                    ['update_type' => 'user_added', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData]
                ),
                UserAddedToChatEvent::class
            ],
            'UserRemovedFromChatUpdate' => [
                Update::newFromData(
                    ['update_type' => 'user_removed', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData]
                ),
                UserRemovedFromChatEvent::class
            ],
        ];
    }

    protected function _before(): void
    {
        $this->apiClient = new MaxApiClient('test-token');
        $this->userData = new ArrayObject();
    }
}
