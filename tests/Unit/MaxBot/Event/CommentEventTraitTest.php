<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Event\PostIdMissingException;
use MaxMessenger\Bot\Exception\MaxBot\Event\SenderUnknownException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentEventTrait;
use MaxMessenger\Bot\Model\Request\NewCommentBody;
use MaxMessenger\Bot\Model\Response\SendCommentResult;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

/**
 * Методы трейта {@see CommentEventTrait} во всех событиях комментариев, которые его используют.
 */
final class CommentEventTraitTest extends Unit
{
    /**
     * @dataProvider updateTypes
     */
    public function testDeleteComment(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createEvent($updateType, $http)->deleteComment();

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
    }

    /**
     * @dataProvider updateTypes
     */
    public function testDeleteCommentWithoutPostId(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $event = $this->createEvent($updateType, $http, self::commentWithoutPostId());

        try {
            $event->deleteComment();
            self::fail('Ожидалась ошибка об отсутствии поста');
        } catch (PostIdMissingException) {
            self::assertSame([], $http->calls);
        }
    }

    /**
     * @dataProvider updateTypes
     */
    public function testEditComment(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createEvent($updateType, $http)->editComment('Новый текст');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('put', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
        self::assertSame('{"text":"Новый текст"}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    /**
     * @dataProvider updateTypes
     */
    public function testEditCommentWithBody(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createEvent($updateType, $http)->editComment(new NewCommentBody('Новый текст'));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('{"text":"Новый текст"}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    /**
     * @dataProvider updateTypes
     */
    public function testEditCommentWithoutPostId(string $updateType): void
    {
        $event = $this->createEvent($updateType, commentData: self::commentWithoutPostId());

        $this->expectException(PostIdMissingException::class);

        $event->editComment('Новый текст');
    }

    /**
     * @dataProvider updateTypes
     */
    public function testGetPostIdWithoutPostId(string $updateType): void
    {
        $event = $this->createEvent($updateType, commentData: self::commentWithoutPostId());

        $this->expectException(PostIdMissingException::class);

        $event->getPostId();
    }

    /**
     * @dataProvider updateTypes
     */
    public function testGetters(string $updateType): void
    {
        $event = $this->createEvent($updateType);

        self::assertSame('комментарий', $event->getComment()->getText());
        self::assertSame('mid.comment', $event->getComment()->getBody()->getMid());
        self::assertSame(1_700_000_000_000, $event->getTimestampRaw());
        self::assertSame(100, $event->getChatId());
        self::assertSame('mid.post', $event->getPostId());
        self::assertNull($event->getUser());
        self::assertNull($event->getUserId());
        self::assertTrue($event->isChannel());
        self::assertFalse($event->isChat());
    }

    /**
     * @dataProvider updateTypes
     */
    public function testGettersWithSender(string $updateType): void
    {
        $event = $this->createEvent($updateType, commentData: self::commentWithSender());

        self::assertSame(100, $event->getChatId());
        self::assertSame(200, $event->getUserId());
        self::assertSame('Иван', $event->getUser()?->getFirstName());
        self::assertFalse($event->isChannel());
        self::assertTrue($event->isChat());
    }

    /**
     * @dataProvider updateTypes
     */
    public function testReply(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::commentData()]);

        $result = $this->createEvent($updateType, $http)->reply('Ответ');

        self::assertInstanceOf(SendCommentResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame('{"text":"Ответ"}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    /**
     * @dataProvider updateTypes
     */
    public function testReplyAsReply(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::commentData()]);

        $this->createEvent($updateType, $http)->reply('Ответ', asReply: true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(
            '{"text":"Ответ","link":{"mid":"mid.comment","type":"reply"}}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );
    }

    /**
     * @dataProvider updateTypes
     */
    public function testReplyAsReplyWithBody(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::commentData()]);
        $comment = new NewCommentBody('Ответ');

        $this->createEvent($updateType, $http)->reply($comment, asReply: true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('mid.comment', $comment->getLink()?->getMid());
        self::assertSame(
            '{"text":"Ответ","link":{"mid":"mid.comment","type":"reply"}}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );
    }

    /**
     * @dataProvider updateTypes
     */
    public function testReplyToUser(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $event = $this->createEvent($updateType, $http, self::commentWithSender());

        $result = $event->replyToUser('Лично', disableLinkPreview: true);

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['user_id' => 200, 'disable_link_preview' => 'true'], $call['query']);
    }

    /**
     * @dataProvider updateTypes
     */
    public function testReplyToUserWithoutSender(string $updateType): void
    {
        $http = new FakeMaxHttpClient();
        $event = $this->createEvent($updateType, $http);

        try {
            $event->replyToUser('Лично');
            self::fail('Ожидалась ошибка о скрытом отправителе');
        } catch (SenderUnknownException) {
            self::assertSame([], $http->calls);
        }
    }

    /**
     * @dataProvider updateTypes
     */
    public function testRequireUser(string $updateType): void
    {
        $event = $this->createEvent($updateType, commentData: self::commentWithSender());

        self::assertSame(200, $event->requireUser()->getUserId());
    }

    /**
     * @dataProvider updateTypes
     */
    public function testRequireUserId(string $updateType): void
    {
        $event = $this->createEvent($updateType, commentData: self::commentWithSender());

        self::assertSame(200, $event->requireUserId());
    }

    /**
     * @dataProvider updateTypes
     */
    public function testRequireUserIdWithoutSender(string $updateType): void
    {
        $event = $this->createEvent($updateType);

        $this->expectException(SenderUnknownException::class);

        $event->requireUserId();
    }

    /**
     * @dataProvider updateTypes
     */
    public function testRequireUserWithoutSender(string $updateType): void
    {
        $event = $this->createEvent($updateType);

        $this->expectException(SenderUnknownException::class);

        $event->requireUser();
    }

    /**
     * @dataProvider updateTypes
     */
    public function testSendMessageToUser(string $updateType): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $event = $this->createEvent($updateType, $http, self::commentWithSender());

        $result = $event->sendMessageToUser('Лично');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['user_id' => 200], $call['query']);
    }

    /**
     * @dataProvider updateTypes
     */
    public function testSendMessageToUserWithoutSender(string $updateType): void
    {
        $http = new FakeMaxHttpClient();
        $event = $this->createEvent($updateType, $http);

        try {
            $event->sendMessageToUser('Лично');
            self::fail('Ожидалась ошибка о скрытом отправителе');
        } catch (SenderUnknownException) {
            self::assertSame([], $http->calls);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function updateTypes(): iterable
    {
        yield 'comment_created' => ['comment_created'];
        yield 'comment_edited' => ['comment_edited'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function commentWithSender(): array
    {
        return [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function commentWithoutPostId(): array
    {
        return [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'channel'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
        ];
    }

    /**
     * @param array<string, mixed>|null $commentData
     */
    private function createEvent(
        string $updateType,
        ?FakeMaxHttpClient $http = null,
        ?array $commentData = null,
    ): CommentCreatedEvent|CommentEditedEvent {
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http ?? new FakeMaxHttpClient());
        $event = BaseEvent::new(Update::newFromData([
            'update_type' => $updateType,
            'timestamp' => 1_700_000_000_000,
            'message' => $commentData ?? FakeMaxHttpClient::commentData(),
        ]), $client, []);
        self::assertTrue($event instanceof CommentCreatedEvent || $event instanceof CommentEditedEvent);

        return $event;
    }
}
