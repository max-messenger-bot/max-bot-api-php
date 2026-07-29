<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\BotCommand;
use MaxMessenger\Bot\Model\Response\BotCommandsInfo;

final class BotCommandsInfoTest extends Unit
{
    public function testGetCommands(): void
    {
        $info = BotCommandsInfo::newFromData([
            'commands' => [
                ['name' => 'start', 'description' => 'Начать работу с ботом'],
                ['name' => 'help'],
            ],
        ]);

        $commands = $info->getCommands();

        self::assertNotNull($commands);
        self::assertCount(2, $commands);
        self::assertInstanceOf(BotCommand::class, $commands[0]);
        self::assertSame('start', $commands[0]->getName());
        self::assertSame('Начать работу с ботом', $commands[0]->getDescription());
        self::assertSame('help', $commands[1]->getName());
        self::assertNull($commands[1]->getDescription());
    }

    public function testGetCommandsCached(): void
    {
        $info = BotCommandsInfo::newFromData([
            'commands' => [['name' => 'start']],
        ]);

        self::assertSame($info->getCommands(), $info->getCommands());
    }

    public function testGetCommandsEmpty(): void
    {
        $info = BotCommandsInfo::newFromData(['commands' => []]);

        self::assertSame([], $info->getCommands());
    }

    public function testGetCommandsMissing(): void
    {
        $info = BotCommandsInfo::newFromData([]);

        self::assertNull($info->getCommands());
    }
}
