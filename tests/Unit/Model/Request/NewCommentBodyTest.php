<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Request\NewCommentBody;
use MaxMessenger\Bot\Model\Request\NewMessageLink;
use MaxMessenger\Bot\Model\Response\CommentMessage;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_UNICODE;

final class NewCommentBodyTest extends Unit
{
    public function testAddLineAndAddText(): void
    {
        $body = (new NewCommentBody('Первая'))->addLine('Вторая')->addText('!');

        self::assertSame("Первая\nВторая!", $body->getText());
    }

    public function testAddLineOnEmptyBody(): void
    {
        $body = (new NewCommentBody())->addLine('Первая');

        self::assertSame('Первая', $body->getText());
    }

    public function testConstructor(): void
    {
        $link = new NewMessageLink('mid.1');
        $body = new NewCommentBody('Текст', $link, TextFormat::Markdown);

        self::assertSame('Текст', $body->getText());
        self::assertSame($link, $body->getLink());
        self::assertSame(TextFormat::Markdown, $body->getFormat());
        self::assertTrue($body->issetText());
        self::assertTrue($body->issetLink());
        self::assertTrue($body->issetFormat());
    }

    public function testEmptyTextIsRejected(): void
    {
        $this->expectException(MinLengthException::class);

        /** @psalm-suppress InvalidArgument Проверяем, что пустой текст комментария отклоняется. */
        new NewCommentBody('');
    }

    public function testJsonSerialize(): void
    {
        $body = NewCommentBody::make('Комментарий', format: TextFormat::Html);

        self::assertSame(
            '{"text":"Комментарий","format":"html"}',
            json_encode($body, JSON_UNESCAPED_UNICODE),
        );
    }

    public function testNewWithoutArguments(): void
    {
        $body = NewCommentBody::new();

        self::assertNull($body->getLink());
        self::assertNull($body->getFormat());
        self::assertFalse($body->issetText());
    }

    public function testSetReplyLinkFromComment(): void
    {
        $comment = CommentMessage::newFromData([
            'recipient' => ['chat_id' => 100, 'chat_type' => 'channel', 'post_id' => 'mid.post'],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.comment', 'seq' => 1, 'text' => 'текст'],
        ]);

        $body = (new NewCommentBody('Ответ'))->setReplyLink($comment);

        $link = $body->getLink();
        self::assertNotNull($link);
        self::assertSame('mid.comment', $link->getMid());
        self::assertSame(MessageLinkType::Reply, $link->getType());
    }

    public function testSetReplyLinkFromString(): void
    {
        $body = (new NewCommentBody('Ответ'))->setReplyLink('mid.comment');

        $link = $body->getLink();
        self::assertNotNull($link);
        self::assertSame('mid.comment', $link->getMid());
    }

    public function testTooLongTextIsRejected(): void
    {
        $this->expectException(MaxLengthException::class);

        new NewCommentBody(str_repeat('a', 4001));
    }

    public function testUnsetLinkAndFormat(): void
    {
        $body = new NewCommentBody('Текст', new NewMessageLink('mid.1'), TextFormat::Markdown);

        $body->unsetLink()->unsetFormat();

        self::assertFalse($body->issetLink());
        self::assertFalse($body->issetFormat());
        self::assertSame('{"text":"Текст"}', json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    public function testValidateRequiredWithoutText(): void
    {
        $body = new NewCommentBody();

        $this->expectException(RequiredFieldException::class);

        $body->validateRequired();
    }
}
