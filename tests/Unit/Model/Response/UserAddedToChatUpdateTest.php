<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\UserAddedToChatUpdate;

final class UserAddedToChatUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate(['is_channel' => true, 'inviter_id' => 300]);

        self::assertSame(100, $update->getChatId());
        self::assertSame(300, $update->getInviterId());
        self::assertTrue($update->isChannel());
        self::assertSame(200, $update->getUser()->getUserId());
        self::assertSame($update->getUser(), $update->getUser());
    }

    public function testWithoutInviter(): void
    {
        $update = $this->createUpdate(['is_channel' => false]);

        self::assertNull($update->getInviterId());
        self::assertFalse($update->isChannel());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdate(array $data): UserAddedToChatUpdate
    {
        $update = Update::newFromData($data + [
            'update_type' => 'user_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ]);
        self::assertInstanceOf(UserAddedToChatUpdate::class, $update);

        return $update;
    }
}
