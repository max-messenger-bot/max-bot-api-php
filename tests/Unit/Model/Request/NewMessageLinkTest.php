<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Request\NewMessageLink;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\CommentMessageBody;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageBody;

use function json_encode;

final class NewMessageLinkTest extends Unit
{
    /**
     * @return iterable<string, array{Message|MessageBody|CommentMessage|CommentMessageBody, non-empty-string}>
     */
    public static function messages(): iterable
    {
        $messageBody = ['mid' => 'mid.message', 'seq' => 1, 'text' => 'сообщение'];
        $commentBody = ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'комментарий'];
        $message = [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'chat'],
            'timestamp' => 1_700_000_000_000,
            'body' => $messageBody,
        ];
        $comment = [
            'recipient' => ['chat_id' => 100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => $commentBody,
        ];

        yield 'Message' => [Message::newFromData($message), 'mid.message'];
        yield 'MessageBody' => [MessageBody::newFromData($messageBody), 'mid.message'];
        yield 'CommentMessage' => [CommentMessage::newFromData($comment), 'mid.comment'];
        yield 'CommentMessageBody' => [CommentMessageBody::newFromData($commentBody), 'mid.comment'];
    }

    public function testConstructorDefaultsToReply(): void
    {
        $link = new NewMessageLink('mid.1');

        self::assertTrue($link->issetMid());
        self::assertSame('mid.1', $link->getMid());
        self::assertSame(MessageLinkType::Reply, $link->getType());
    }

    public function testEmptyMidIsRejected(): void
    {
        $this->expectException(MatchException::class);
        $this->expectExceptionMessage('Argument "mid" has an invalid value.');

        /** @psalm-suppress InvalidArgument */
        new NewMessageLink('');
    }

    public function testInvalidMidIsRejected(): void
    {
        $this->expectException(MatchException::class);
        $this->expectExceptionMessage('Argument "mid" has an invalid value.');

        NewMessageLink::make('mid.with space', MessageLinkType::Forward);
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"mid":"mid.abc_1-2","type":"forward"}',
            json_encode(NewMessageLink::make('mid.abc_1-2', MessageLinkType::Forward)),
        );
    }

    public function testJsonSerializeWithoutMid(): void
    {
        $link = NewMessageLink::new();

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "mid" field cannot be null.');

        $link->jsonSerialize();
    }

    public function testMake(): void
    {
        $link = NewMessageLink::make('mid.1', MessageLinkType::Forward);

        self::assertSame('mid.1', $link->getMid());
        self::assertSame(MessageLinkType::Forward, $link->getType());
    }

    /**
     * @param non-empty-string $mid
     * @dataProvider messages
     */
    public function testNewFromMessage(
        Message|MessageBody|CommentMessage|CommentMessageBody $message,
        string $mid,
    ): void {
        $reply = NewMessageLink::newFromMessage($message);
        $forward = NewMessageLink::newFromMessage($message, MessageLinkType::Forward);

        self::assertSame($mid, $reply->getMid());
        self::assertSame(MessageLinkType::Reply, $reply->getType());
        self::assertSame($mid, $forward->getMid());
        self::assertSame(MessageLinkType::Forward, $forward->getType());
    }

    public function testNewWithoutMid(): void
    {
        $link = NewMessageLink::new();

        self::assertFalse($link->issetMid());
        self::assertSame(MessageLinkType::Reply, $link->getType());
    }

    public function testSetters(): void
    {
        $link = NewMessageLink::new();

        self::assertSame($link, $link->setMid('mid.2'));
        self::assertSame($link, $link->setType(MessageLinkType::Forward));
        self::assertSame('mid.2', $link->getMid());
        self::assertSame(MessageLinkType::Forward, $link->getType());
    }
}
