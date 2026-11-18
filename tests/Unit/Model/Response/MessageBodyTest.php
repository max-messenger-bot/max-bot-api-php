<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\Attachment;
use MaxMessenger\Bot\Model\Response\MessageBody;
use MaxMessenger\Bot\Model\Response\StrongMarkup;
use MaxMessenger\Bot\Model\Response\UserMentionMarkup;

final class MessageBodyTest extends Unit
{
    public function testAttachmentsAndMarkup(): void
    {
        $body = MessageBody::newFromData([
            'mid' => 'mid.1',
            'seq' => 2,
            'text' => 'Привет, Иван',
            'attachments' => [
                ['type' => 'location', 'latitude' => 55.75, 'longitude' => 37.61],
                ['type' => 'unknown_attachment'],
            ],
            'markup' => [
                ['type' => 'strong', 'from' => 0, 'length' => 6],
                ['type' => 'user_mention', 'from' => 8, 'length' => 4, 'user_id' => 200],
            ],
        ]);

        $attachments = $body->getAttachments();
        self::assertNotNull($attachments);
        self::assertCount(2, $attachments);
        self::assertContainsOnlyInstancesOf(Attachment::class, $attachments);
        self::assertSame('location', $attachments[0]->getTypeRaw());
        self::assertSame('unknown_attachment', $attachments[1]->getTypeRaw());
        self::assertSame($attachments, $body->getAttachments());

        $markup = $body->getMarkup();
        self::assertNotNull($markup);
        self::assertCount(2, $markup);
        self::assertInstanceOf(StrongMarkup::class, $markup[0]);
        self::assertInstanceOf(UserMentionMarkup::class, $markup[1]);
        self::assertSame($markup, $body->getMarkup());
    }

    public function testEmptyLists(): void
    {
        $body = MessageBody::newFromData([
            'mid' => 'mid.1',
            'seq' => 1,
            'text' => 'Привет',
            'attachments' => [],
            'markup' => [],
        ]);

        self::assertSame([], $body->getAttachments());
        self::assertSame([], $body->getMarkup());
    }

    public function testGetters(): void
    {
        $body = MessageBody::newFromData(['mid' => 'mid.1', 'seq' => 7, 'text' => 'Привет']);

        self::assertSame('mid.1', $body->getMid());
        self::assertSame(7, $body->getSeq());
        self::assertSame('Привет', $body->getText());
        self::assertNull($body->getAttachments());
        self::assertNull($body->getMarkup());
    }

    public function testTextDefaultsToEmptyString(): void
    {
        $body = MessageBody::newFromData(['mid' => 'mid.media', 'seq' => 1]);

        self::assertSame('', $body->getText());
    }
}
