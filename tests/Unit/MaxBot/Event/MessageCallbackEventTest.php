<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\Model\Response\Callback;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

final class MessageCallbackEventTest extends Unit
{
    public function testAnswer(): void
    {
        $http = new FakeMaxHttpClient();
        $this->createEventWithHttp($http)->answer('Готово');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/answers', $call['path']);
        self::assertSame(['callback_id' => 'cb.1'], $call['query']);
    }

    public function testAnswerNotification(): void
    {
        $http = new FakeMaxHttpClient();
        $this->createEventWithHttp($http)->answerNotification('Уведомление');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/answers', $call['path']);
        self::assertSame(['callback_id' => 'cb.1'], $call['query']);
    }

    public function testGetCallback(): void
    {
        $callback = $this->createEvent()->getCallback();

        self::assertInstanceOf(Callback::class, $callback);
        self::assertSame('action1', $callback->getPayload());
    }

    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetMessage(): void
    {
        self::assertInstanceOf(Message::class, $this->createEvent()->getMessage());
    }

    public function testGetUser(): void
    {
        $user = $this->createEvent()->getUser();

        self::assertInstanceOf(User::class, $user);
        self::assertSame(200, $user->getUserId());
    }

    public function testGetUserId(): void
    {
        self::assertSame(200, $this->createEvent()->getUserId());
    }

    public function testGetUserLocale(): void
    {
        self::assertSame('ru-RU', $this->createEvent(userLocale: 'ru-RU')->getUserLocale());
    }

    public function testNoUserLocale(): void
    {
        self::assertNull($this->createEvent()->getUserLocale());
    }

    private function createEvent(?string $userLocale = null): MessageCallbackEvent
    {
        $message = [
            'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
        ];

        $callback = [
            'timestamp' => 1_700_000_000_000,
            'callback_id' => 'cb.1',
            'payload' => 'action1',
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ];

        $data = [
            'update_type' => 'message_callback',
            'timestamp' => 1_700_000_000_000,
            'callback' => $callback,
            'message' => $message,
        ];
        if ($userLocale !== null) {
            $data['user_locale'] = $userLocale;
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(MessageCallbackEvent::class, $event);

        return $event;
    }

    private function createEventWithHttp(FakeMaxHttpClient $http): MessageCallbackEvent
    {
        $client = new MaxApiClient(new FakeMaxApiConfig($http));
        $data = [
            'update_type' => 'message_callback',
            'timestamp' => 1_700_000_000_000,
            'callback' => [
                'timestamp' => 1_700_000_000_000,
                'callback_id' => 'cb.1',
                'payload' => 'action1',
                'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            ],
            'message' => [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            ],
        ];

        $event = BaseEvent::new(Update::newFromData($data), $client, []);
        self::assertInstanceOf(MessageCallbackEvent::class, $event);

        return $event;
    }
}
