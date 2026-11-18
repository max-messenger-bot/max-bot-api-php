<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\DialogMutedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class DialogMutedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate(['user_locale' => 'ru']);

        self::assertSame(100, $update->getChatId());
        self::assertSame(1_700_003_600_500, $update->getMutedUntilRaw());
        self::assertSame('2023-11-14 23:13:20.500 +00:00', $update->getMutedUntil()->format('Y-m-d H:i:s.v P'));
        self::assertSame('ru', $update->getUserLocale());
        self::assertSame(200, $update->getUser()->getUserId());
        self::assertSame($update->getUser(), $update->getUser());
    }

    public function testWithoutUserLocale(): void
    {
        self::assertNull($this->createUpdate([])->getUserLocale());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdate(array $data): DialogMutedUpdate
    {
        $update = Update::newFromData($data + [
            'update_type' => 'dialog_muted',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'muted_until' => 1_700_003_600_500,
        ]);
        self::assertInstanceOf(DialogMutedUpdate::class, $update);

        return $update;
    }
}
