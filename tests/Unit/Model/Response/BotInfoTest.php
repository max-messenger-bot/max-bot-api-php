<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\BotCommand;
use MaxMessenger\Bot\Model\Response\BotInfo;
use MaxMessenger\Bot\Model\Response\UserWithPhoto;

final class BotInfoTest extends Unit
{
    public function testCommands(): void
    {
        $bot = BotInfo::newFromData([
            'user_id' => 300,
            'first_name' => 'Бот',
            'username' => 'test_bot',
            'is_bot' => true,
            'description' => 'Тестовый бот',
            'avatar_url' => 'https://example.test/bot.jpg',
            'commands' => [
                ['name' => 'start', 'description' => 'Запуск'],
                ['name' => 'help'],
            ],
        ]);

        self::assertInstanceOf(UserWithPhoto::class, $bot);
        self::assertTrue($bot->isBot());
        self::assertSame('test_bot', $bot->getUsername());
        self::assertSame('Тестовый бот', $bot->getDescription());
        self::assertSame('https://example.test/bot.jpg', $bot->getAvatarUrl());

        $commands = $bot->getCommands();
        self::assertNotNull($commands);
        self::assertCount(2, $commands);
        self::assertContainsOnlyInstancesOf(BotCommand::class, $commands);
        self::assertSame('start', $commands[0]->getName());
        self::assertSame('Запуск', $commands[0]->getDescription());
        self::assertSame('help', $commands[1]->getName());
        self::assertSame($commands, $bot->getCommands());
    }

    public function testWithoutCommands(): void
    {
        $bot = BotInfo::newFromData(['user_id' => 300, 'first_name' => 'Бот', 'is_bot' => true]);

        self::assertNull($bot->getCommands());
    }
}
