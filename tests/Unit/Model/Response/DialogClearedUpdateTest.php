<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\DialogClearedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class DialogClearedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate(['user_locale' => 'ru']);

        self::assertSame(100, $update->getChatId());
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
    private function createUpdate(array $data): DialogClearedUpdate
    {
        $update = Update::newFromData($data + [
            'update_type' => 'dialog_cleared',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ]);
        self::assertInstanceOf(DialogClearedUpdate::class, $update);

        return $update;
    }
}
