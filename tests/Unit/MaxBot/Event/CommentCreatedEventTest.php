<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Event\PostIdMissingException;
use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentCreatedEvent;
use MaxMessenger\Bot\Model\Response\SendCommentResult;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class CommentCreatedEventTest extends Unit
{
    public function testDeleteComment(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $event = $this->createEvent($http);

        $event->deleteComment();

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
    }

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
    }

    public function testGetPostIdWithoutPostId(): void
    {
        $event = $this->createEvent(commentData: [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'channel'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
        ]);

        $this->expectException(PostIdMissingException::class);

        $event->getPostId();
    }

    public function testGetters(): void
    {
        $event = $this->createEvent();

        self::assertSame('комментарий', $event->getComment()->getText());
        self::assertSame(100, $event->getChatId());
        self::assertSame('mid.post', $event->getPostId());
        self::assertNull($event->getUser());
        self::assertNull($event->getUserId());
        self::assertTrue($event->isChannel());
        self::assertFalse($event->isChat());
        self::assertSame(1_700_000_000_000, $event->getTimestampRaw());
    }

    public function testGettersWithSender(): void
    {
        $event = $this->createEvent(commentData: [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            'sender' => ['user_id' => 200, 'first_name' => 'Бот', 'is_bot' => true],
        ]);

        self::assertSame(200, $event->getUserId());
        self::assertSame('Бот', $event->getUser()?->getFirstName());
        self::assertTrue($event->isChat());
        self::assertFalse($event->isChannel());
    }

    public function testReply(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::commentData()]);
        $event = $this->createEvent($http);

        $result = $event->reply('Ответ');

        self::assertInstanceOf(SendCommentResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame('{"text":"Ответ"}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    public function testReplyAsReply(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::commentData()]);
        $event = $this->createEvent($http);

        $event->reply('Ответ', asReply: true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(
            '{"text":"Ответ","link":{"mid":"mid.comment","type":"reply"}}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );
    }

    public function testReplyToUser(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $data = [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            'sender' => ['user_id' => 200, 'first_name' => 'Бот', 'is_bot' => true],
        ];

        $result = $this->createEvent($http, $data)->replyToUser('Личное сообщение');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['user_id' => 200], $call['query']);
    }

    public function testReplyToUserWithoutSender(): void
    {
        $event = $this->createEvent();

        $this->expectException(SenderUnknownException::class);

        $event->replyToUser('Личное сообщение');
    }

    public function testRequireUser(): void
    {
        $data = [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            'sender' => ['user_id' => 200, 'first_name' => 'Бот', 'is_bot' => true],
        ];

        self::assertSame(200, $this->createEvent(commentData: $data)->requireUser()->getUserId());
    }

    public function testRequireUserId(): void
    {
        $data = [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            'sender' => ['user_id' => 200, 'first_name' => 'Бот', 'is_bot' => true],
        ];

        self::assertSame(200, $this->createEvent(commentData: $data)->requireUserId());
    }

    public function testRequireUserIdWithoutSender(): void
    {
        $event = $this->createEvent();

        $this->expectException(SenderUnknownException::class);

        $event->requireUserId();
    }

    public function testRequireUserWithoutSender(): void
    {
        $event = $this->createEvent();

        $this->expectException(SenderUnknownException::class);

        $event->requireUser();
    }

    public function testSendMessageToUser(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $data = [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            'sender' => ['user_id' => 200, 'first_name' => 'Бот', 'is_bot' => true],
        ];

        $result = $this->createEvent($http, $data)->sendMessageToUser('Личное сообщение');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['user_id' => 200], $call['query']);
    }

    public function testSendMessageToUserWithoutSender(): void
    {
        $event = $this->createEvent();

        $this->expectException(SenderUnknownException::class);

        $event->sendMessageToUser('Личное сообщение');
    }

    /**
     * @param array<string, mixed>|null $commentData
     */
    private function createEvent(?FakeMaxHttpClient $http = null, ?array $commentData = null): CommentCreatedEvent
    {
        $client = new MaxApiClient(new FakeMaxApiConfig($http ?? new FakeMaxHttpClient()));
        $event = BaseEvent::new(Update::newFromData([
            'update_type' => 'comment_created',
            'timestamp' => 1_700_000_000_000,
            'message' => $commentData ?? FakeMaxHttpClient::commentData(),
        ]), $client, []);
        self::assertInstanceOf(CommentCreatedEvent::class, $event);

        return $event;
    }
}
