<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentEditedEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class CommentEditedEventTest extends Unit
{
    public function testEditComment(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $event = $this->createEvent($http);

        $event->editComment('Новый текст');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('put', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
        self::assertSame('{"text":"Новый текст"}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    public function testGetters(): void
    {
        $event = $this->createEvent();

        self::assertSame('комментарий', $event->getComment()->getText());
        self::assertSame('mid.comment', $event->getComment()->getBody()->getMid());
        self::assertSame(100, $event->getChatId());
        self::assertSame('mid.post', $event->getPostId());
        self::assertNull($event->getUser());
        self::assertNull($event->getUserId());
        self::assertTrue($event->isChannel());
        self::assertFalse($event->isChat());
    }

    private function createEvent(?FakeMaxHttpClient $http = null): CommentEditedEvent
    {
        $client = new MaxApiClient(new FakeMaxApiConfig($http ?? new FakeMaxHttpClient()));
        $event = BaseEvent::new(Update::newFromData([
            'update_type' => 'comment_edited',
            'timestamp' => 1_700_000_000_000,
            'message' => FakeMaxHttpClient::commentData(),
        ]), $client, []);
        self::assertInstanceOf(CommentEditedEvent::class, $event);

        return $event;
    }
}
