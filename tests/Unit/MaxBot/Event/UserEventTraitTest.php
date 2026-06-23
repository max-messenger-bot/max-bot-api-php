<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\UserEventTrait;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

/**
 * Сетевые методы трейта {@see UserEventTrait} через фейковый HTTP-клиент.
 *
 * Трейт подключается, в частности, к {@see BotStartedEvent}.
 */
final class UserEventTraitTest extends Unit
{
    public function testSendToChat(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->sendToChat('Привет');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 100], $call['query']);
    }

    public function testSendToUser(): void
    {
        $http = new FakeMaxHttpClient();
        $result = $this->createEvent($http)->sendToUser('Привет');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['user_id' => 200], $call['query']);
    }

    private function createEvent(FakeMaxHttpClient $http): BotStartedEvent
    {
        $client = new MaxApiClient(new FakeMaxApiConfig($http));
        $data = [
            'update_type' => 'bot_started',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ];

        $event = BaseEvent::new(Update::newFromData($data), $client, []);
        self::assertInstanceOf(BotStartedEvent::class, $event);

        return $event;
    }
}
