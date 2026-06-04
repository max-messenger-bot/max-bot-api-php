<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageRemovedEvent;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

final class MessageRemovedEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetMessageId(): void
    {
        self::assertSame('mid.1', $this->createEvent()->getMessageId());
    }

    public function testGetUser(): void
    {
        self::assertNull($this->createEvent()->getUser());
    }

    public function testGetUserId(): void
    {
        self::assertSame(200, $this->createEvent()->getUserId());
    }

    public function testSendMessage(): void
    {
        $http = new FakeMaxHttpClient();
        $client = new MaxApiClient(new FakeMaxApiConfig($http));
        $event = BaseEvent::new(Update::newFromData([
            'update_type' => 'message_removed',
            'timestamp' => 1_700_000_000_000,
            'message_id' => 'mid.1',
            'chat_id' => 100,
            'user_id' => 200,
        ]), $client, []);
        self::assertInstanceOf(MessageRemovedEvent::class, $event);

        $result = $event->sendMessage('Привет');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['user_id' => 200], $call['query']);
    }

    private function createEvent(): MessageRemovedEvent
    {
        $data = [
            'update_type' => 'message_removed',
            'timestamp' => 1_700_000_000_000,
            'message_id' => 'mid.1',
            'chat_id' => 100,
            'user_id' => 200,
        ];

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(MessageRemovedEvent::class, $event);

        return $event;
    }
}
