<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\CommentRemovedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class CommentRemovedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = Update::newFromData([
            'update_type' => 'comment_removed',
            'timestamp' => 1_700_000_000_000,
            'message_id' => 'mid.comment',
            'chat_id' => 100,
            'user_id' => 200,
            'post_id' => 'mid.post',
        ]);
        self::assertInstanceOf(CommentRemovedUpdate::class, $update);

        self::assertSame(100, $update->getChatId());
        self::assertSame('mid.comment', $update->getMessageId());
        self::assertSame('mid.post', $update->getPostId());
        self::assertSame(200, $update->getUserId());
    }

    public function testPostIdCanBeMissingOrNull(): void
    {
        $data = [
            'update_type' => 'comment_removed',
            'timestamp' => 1,
            'message_id' => 'mid.comment',
            'chat_id' => 100,
            'user_id' => 200,
        ];

        foreach ([$data, $data + ['post_id' => null]] as $payload) {
            $update = Update::newFromData($payload);
            self::assertInstanceOf(CommentRemovedUpdate::class, $update);
            self::assertTrue($update->isValid());
            self::assertNull($update->getPostId());
        }
    }
}
