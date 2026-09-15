<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentRemovedEvent;
use MaxMessenger\Bot\Model\Response\SendCommentResult;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class CommentRemovedEventTest extends Unit
{
    public function testGetters(): void
    {
        $event = $this->createEvent();

        self::assertSame(100, $event->getChatId());
        self::assertSame('mid.comment', $event->getMessageId());
        self::assertSame('mid.post', $event->getPostId());
        self::assertSame(200, $event->getUserId());
        self::assertNull($event->getUser());
    }

    public function testReply(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::commentData()]);
        $event = $this->createEvent($http);

        $result = $event->reply('Комментарий восстановлению не подлежит');

        self::assertInstanceOf(SendCommentResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(
            '{"text":"Комментарий восстановлению не подлежит"}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );
    }

    public function testSendMessageToUser(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $event = $this->createEvent($http);

        $result = $event->sendMessageToUser('Комментарий удалён');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['user_id' => 200], $call['query']);
    }

    private function createEvent(?FakeMaxHttpClient $http = null): CommentRemovedEvent
    {
        $client = new MaxApiClient(new FakeMaxApiConfig($http ?? new FakeMaxHttpClient()));
        $data = [
            'update_type' => 'comment_removed',
            'timestamp' => 1_700_000_000_000,
            'message_id' => 'mid.comment',
            'chat_id' => 100,
            'user_id' => 200,
            'post_id' => 'mid.post',
        ];

        $event = BaseEvent::new(Update::newFromData($data), $client, []);
        self::assertInstanceOf(CommentRemovedEvent::class, $event);

        return $event;
    }
}
