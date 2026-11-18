<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\BotCommand;

final class BotCommandTest extends Unit
{
    public function testWithDescription(): void
    {
        $command = BotCommand::newFromData(['name' => 'start', 'description' => 'Запуск бота']);

        self::assertSame('start', $command->getName());
        self::assertSame('Запуск бота', $command->getDescription());
    }

    public function testWithoutDescription(): void
    {
        $command = BotCommand::newFromData(['name' => 'help']);

        self::assertSame('help', $command->getName());
        self::assertNull($command->getDescription());
    }
}
