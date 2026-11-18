<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\CommentMessageBody;
use MaxMessenger\Bot\Model\Response\LinkMarkup;

final class CommentMessageBodyTest extends Unit
{
    public function testGetters(): void
    {
        $body = CommentMessageBody::newFromData(['mid' => 'mid.comment', 'seq' => 5, 'text' => 'комментарий']);

        self::assertSame('mid.comment', $body->getMid());
        self::assertSame(5, $body->getSeq());
        self::assertSame('комментарий', $body->getText());
        self::assertNull($body->getMarkup());
    }

    public function testMarkup(): void
    {
        $body = CommentMessageBody::newFromData([
            'mid' => 'mid.comment',
            'seq' => 1,
            'text' => 'сайт',
            'markup' => [['type' => 'link', 'from' => 0, 'length' => 4, 'url' => 'https://example.test']],
        ]);

        $markup = $body->getMarkup();
        self::assertNotNull($markup);
        self::assertCount(1, $markup);
        self::assertInstanceOf(LinkMarkup::class, $markup[0]);
        self::assertSame('https://example.test', $markup[0]->getUrl());
        self::assertSame($markup, $body->getMarkup());
    }
}
