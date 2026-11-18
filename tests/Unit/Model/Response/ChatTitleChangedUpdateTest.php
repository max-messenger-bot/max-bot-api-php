<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\ChatTitleChangedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class ChatTitleChangedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = Update::newFromData([
            'update_type' => 'chat_title_changed',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'title' => 'Новое название',
        ]);
        self::assertInstanceOf(ChatTitleChangedUpdate::class, $update);

        self::assertSame(100, $update->getChatId());
        self::assertSame('Новое название', $update->getTitle());
        self::assertSame(200, $update->getUser()->getUserId());
        self::assertSame($update->getUser(), $update->getUser());
    }
}
