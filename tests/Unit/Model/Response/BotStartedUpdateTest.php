<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\BotStartedUpdate;
use MaxMessenger\Bot\Model\Response\Update;

final class BotStartedUpdateTest extends Unit
{
    public function testGetters(): void
    {
        $update = $this->createUpdate(['payload' => 'start-payload', 'user_locale' => 'ru']);

        self::assertSame(100, $update->getChatId());
        self::assertSame('start-payload', $update->getPayload());
        self::assertSame('ru', $update->getUserLocale());
        self::assertSame(200, $update->getUser()->getUserId());
        self::assertSame($update->getUser(), $update->getUser());
    }

    public function testWithoutOptionalFields(): void
    {
        $update = $this->createUpdate([]);

        self::assertNull($update->getPayload());
        self::assertNull($update->getUserLocale());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdate(array $data): BotStartedUpdate
    {
        $update = Update::newFromData($data + [
            'update_type' => 'bot_started',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ]);
        self::assertInstanceOf(BotStartedUpdate::class, $update);

        return $update;
    }
}
