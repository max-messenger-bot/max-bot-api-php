<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEventTrait;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

/**
 * Сетевые методы трейта {@see MessageEventTrait} через фейковый HTTP-клиент.
 */
final class MessageEventTraitTest extends Unit
{
    public function testDeleteMessage(): void
    {
        $http = new FakeMaxHttpClient();
        $this->createEvent($http)->deleteMessage();

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['message_id' => 'mid.1'], $call['query']);
    }

    public function testForwardToChat(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->forwardToChat(555);

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 555], $call['query']);
    }

    public function testForwardToUser(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->forwardToUser(777);

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['user_id' => 777], $call['query']);
    }

    public function testReply(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->reply('Ответ');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 100], $call['query']);
    }

    public function testReplyAsReply(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->reply('Ответ', asReply: true);

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 100], $call['query']);
    }

    public function testReplyToUser(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->replyToUser('Лично');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['user_id' => 200], $call['query']);
    }

    public function testSendAction(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->sendAction(SenderAction::TypingOn);

        self::assertTrue($result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/chats/100/actions', $call['path']);
    }

    private function createEvent(FakeMaxHttpClient $http): MessageCreatedEvent
    {
        $client = new MaxApiClient(new FakeMaxApiConfig($http));
        $data = [
            'update_type' => 'message_created',
            'timestamp' => 1_700_000_000_000,
            'message' => [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            ],
        ];

        $event = BaseEvent::new(Update::newFromData($data), $client, []);
        self::assertInstanceOf(MessageCreatedEvent::class, $event);

        return $event;
    }
}
