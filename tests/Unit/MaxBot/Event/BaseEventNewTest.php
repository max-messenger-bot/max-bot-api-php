<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use ArrayObject;
use Closure;
use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStoppedEvent;
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
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
use MaxMessenger\Bot\Model\Response\Update;
use Throwable;

use function time;

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
                'timestamp' => $timestamp,
            ],
        ]);
        $userData = new ArrayObject(['key' => 'value']);
        $exceptionHandlers = [
            static function (Throwable $_e, BaseEvent $_event): void {},
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
                    ['update_type' => 'bot_added', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData],
                ),
                BotAddedToChatEvent::class,
            ],
            'BotRemovedFromChatUpdate' => [
                Update::newFromData(
                    ['update_type' => 'bot_removed', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData],
                ),
                BotRemovedFromChatEvent::class,
            ],
            'BotStartedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'bot_started', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData],
                ),
                BotStartedEvent::class,
            ],
            'BotStoppedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'bot_stopped', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData],
                ),
                BotStoppedEvent::class,
            ],
            'ChatTitleChangedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'chat_title_changed',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'title' => 'New Title',
                        'user' => $userData,
                    ],
                ),
                ChatTitleChangedEvent::class,
            ],
            'DialogClearedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_cleared',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'user' => $userData,
                    ],
                ),
                DialogClearedEvent::class,
            ],
            'DialogMutedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_muted',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'muted_until' => $timestamp,
                        'user' => $userData,
                    ],
                ),
                DialogMutedEvent::class,
            ],
            'DialogRemovedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_removed',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'user' => $userData,
                    ],
                ),
                DialogRemovedEvent::class,
            ],
            'DialogUnmutedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'dialog_unmuted',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'user' => $userData,
                    ],
                ),
                DialogUnmutedEvent::class,
            ],
            'MessageCallbackUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'message_callback',
                        'timestamp' => $timestamp,
                        'message' => $messageData,
                        'callback' => ['callback_id' => 'test', 'payload' => 'test', 'user' => $userData],
                    ],
                ),
                MessageCallbackEvent::class,
            ],
            'MessageCreatedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'message_created', 'timestamp' => $timestamp, 'message' => $messageData],
                ),
                MessageCreatedEvent::class,
            ],
            'MessageEditedUpdate' => [
                Update::newFromData(
                    ['update_type' => 'message_edited', 'timestamp' => $timestamp, 'message' => $messageData],
                ),
                MessageEditedEvent::class,
            ],
            'MessageRemovedUpdate' => [
                Update::newFromData(
                    [
                        'update_type' => 'message_removed',
                        'timestamp' => $timestamp,
                        'chat_id' => 123,
                        'message_id' => 'mid.test',
                        'user_id' => 123,
                    ],
                ),
                MessageRemovedEvent::class,
            ],
            'UserAddedToChatUpdate' => [
                Update::newFromData(
                    ['update_type' => 'user_added', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData],
                ),
                UserAddedToChatEvent::class,
            ],
            'UserRemovedFromChatUpdate' => [
                Update::newFromData(
                    ['update_type' => 'user_removed', 'timestamp' => $timestamp, 'chat_id' => 123, 'user' => $userData],
                ),
                UserRemovedFromChatEvent::class,
            ],
        ];
    }

    protected function _before(): void
    {
        $this->apiClient = new MaxApiClient('test-token');
        $this->userData = new ArrayObject();
    }
}
