<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Response\CommentLinkedMessage;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\CommentMessageBody;
use MaxMessenger\Bot\Model\Response\CommentMessageList;
use MaxMessenger\Bot\Model\Response\SendCommentResult;
use MaxMessenger\Bot\Model\Response\UserMentionMarkup;

final class CommentMessageTest extends Unit
{
    public function testCommentFromChannel(): void
    {
        $comment = CommentMessage::newFromData(self::commentData());

        self::assertSame('mid.comment', $comment->getBody()->getMid());
        self::assertSame(1, $comment->getBody()->getSeq());
        self::assertSame('комментарий', $comment->getText());
        self::assertSame(1_700_000_000_000, $comment->getTimestampRaw());
        self::assertSame('2023-11-14 22:13:20', $comment->getTimestamp()->format('Y-m-d H:i:s'));
        self::assertSame(ChatType::Channel, $comment->getRecipient()->getChatType());
        self::assertSame('mid.post', $comment->getRecipient()->getPostId());
        self::assertNull($comment->getSender());
        self::assertNull($comment->getLink());
        self::assertNull($comment->getUrl());
        self::assertNull($comment->getBody()->getMarkup());
    }

    public function testCommentWithLink(): void
    {
        $link = CommentMessage::newFromData(
            self::commentData() + [
                'link' => [
                    'type' => 'reply',
                    'sender' => ['user_id' => 200, 'first_name' => 'Пользователь', 'is_bot' => false],
                    'chat_id' => 100,
                    'message' => ['mid' => 'mid.parent', 'seq' => 0, 'text' => 'родительский'],
                ],
            ],
        )->getLink();

        self::assertInstanceOf(CommentLinkedMessage::class, $link);
        self::assertSame(MessageLinkType::Reply, $link->getType());
        self::assertTrue($link->isReply());
        self::assertFalse($link->isForward());
        self::assertSame(100, $link->getChatId());
        self::assertSame('Пользователь', $link->getSender()?->getFirstName());

        $parent = $link->getMessage();
        self::assertInstanceOf(CommentMessageBody::class, $parent);
        self::assertSame('mid.parent', $parent->getMid());
    }

    public function testCommentWithMarkupAndUrl(): void
    {
        $comment = CommentMessage::newFromData([
            'recipient' => ['chat_id' => 100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => [
                'mid' => 'mid.comment',
                'seq' => 1,
                'text' => 'комментарий',
                'markup' => [['type' => 'user_mention', 'from' => 0, 'length' => 5, 'user_id' => 200]],
            ],
            'url' => 'https://max.ru/channel/AaBk496dDXA',
        ]);

        self::assertSame('https://max.ru/channel/AaBk496dDXA', $comment->getUrl());

        $markup = $comment->getBody()->getMarkup();
        self::assertNotNull($markup);
        self::assertCount(1, $markup);
        self::assertInstanceOf(UserMentionMarkup::class, $markup[0]);
        self::assertSame(200, $markup[0]->getUserId());
    }

    public function testCommentWithSender(): void
    {
        $comment = CommentMessage::newFromData([
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
            'sender' => ['user_id' => 200, 'first_name' => 'Бот', 'is_bot' => true],
        ]);

        self::assertSame(ChatType::Chat, $comment->getRecipient()->getChatType());
        self::assertSame(200, $comment->getSender()?->getUserId());
        self::assertTrue($comment->getSender()?->isBot());
    }

    public function testList(): void
    {
        $list = CommentMessageList::newFromData(['messages' => [self::commentData(), self::commentData()]]);

        $comments = $list->getMessages();
        self::assertCount(2, $comments);
        self::assertSame('mid.comment', $comments[0]->getBody()->getMid());
        self::assertSame($comments, $list->getMessages());
    }

    public function testSendCommentResult(): void
    {
        $result = SendCommentResult::newFromData(['message' => self::commentData()]);

        $comment = $result->getMessage();
        self::assertSame('комментарий', $comment->getText());
        self::assertSame($comment, $result->getMessage());
    }

    /**
     * @return array<string, mixed>
     */
    private static function commentData(): array
    {
        return [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'],
        ];
    }
}
