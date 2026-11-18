<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\ActionProhibited;
use MaxMessenger\Bot\Model\Request\BaseRequestModel;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\RawModel;

use function iterator_to_array;

/**
 * Общее поведение {@see BaseRequestModel} на примере {@see BotCommand}.
 */
final class BaseRequestModelTest extends Unit
{
    public function testGetIterator(): void
    {
        $command = BotCommand::make('start', 'Начать');

        self::assertSame(['name' => 'start', 'description' => 'Начать'], iterator_to_array($command));
    }

    public function testGetRawData(): void
    {
        self::assertSame(['name' => 'start'], BotCommand::make('start')->getRawData());
        self::assertSame([], BotCommand::new()->getRawData());
    }

    public function testGetRawModel(): void
    {
        $command = BotCommand::make('start', 'Начать');

        $raw = $command->getRawModel();

        self::assertInstanceOf(RawModel::class, $raw);
        self::assertNotSame($command, $raw);
        self::assertSame($command->getRawData(), $raw->getRawData());
    }

    public function testOffsetExists(): void
    {
        $command = BotCommand::make('start');

        self::assertTrue(isset($command['name']));
        self::assertFalse(isset($command['description']));
    }

    public function testOffsetGet(): void
    {
        $command = BotCommand::make('start');

        self::assertSame('start', $command['name']);
        self::assertNull($command['description']);
    }

    public function testOffsetSetIsProhibited(): void
    {
        $command = BotCommand::make('start');

        try {
            $command['name'] = 'help';
            self::fail('Ожидался запрет на изменение поля');
        } catch (ActionProhibited $e) {
            self::assertSame('Action prohibited.', $e->getMessage());
            self::assertSame('start', $command->getName());
        }
    }

    public function testOffsetUnsetIsProhibited(): void
    {
        $command = BotCommand::make('start', 'Начать');

        try {
            unset($command['description']);
            self::fail('Ожидался запрет на удаление поля');
        } catch (ActionProhibited $e) {
            self::assertSame('Action prohibited.', $e->getMessage());
            self::assertSame('Начать', $command->getDescription());
        }
    }
}
