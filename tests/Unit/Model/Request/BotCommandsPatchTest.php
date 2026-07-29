<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxItemsException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class BotCommandsPatchTest extends Unit
{
    public function testAddCommand(): void
    {
        $model = BotCommandsPatch::new();

        $result = $model->addCommand(new BotCommand('start', 'Начать работу с ботом'));
        $model->addCommand(new BotCommand('help'));

        self::assertSame($model, $result);
        $commands = $model->getCommands();
        self::assertCount(2, $commands);
        self::assertSame('start', $commands[0]->getName());
        self::assertSame('help', $commands[1]->getName());
    }

    public function testAddCommandOverLimit(): void
    {
        $model = BotCommandsPatch::make(self::makeCommands(32));

        $this->expectException(MaxItemsException::class);

        $model->addCommand(new BotCommand('command32'));
    }

    public function testConstructor(): void
    {
        $model = new BotCommandsPatch([new BotCommand('start', 'Начать работу с ботом')]);

        self::assertTrue($model->issetCommands());
        self::assertCount(1, $model->getCommands());
        self::assertSame('start', $model->getCommands()[0]->getName());
    }

    public function testEmptyCommandsList(): void
    {
        $model = BotCommandsPatch::make([]);

        self::assertTrue($model->issetCommands());
        self::assertSame([], $model->getCommands());
        self::assertSame('{"commands":[]}', json_encode($model->jsonSerialize()));
    }

    public function testJsonSerialize(): void
    {
        $model = BotCommandsPatch::make([new BotCommand('start', 'Начать работу с ботом')]);

        self::assertSame(
            '{"commands":[{"name":"start","description":"Начать работу с ботом"}]}',
            json_encode($model->jsonSerialize(), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testJsonSerializeWithoutCommands(): void
    {
        $model = BotCommandsPatch::new();

        $this->expectException(RequiredFieldException::class);

        $model->jsonSerialize();
    }

    public function testNewWithoutCommands(): void
    {
        $model = BotCommandsPatch::new();

        self::assertFalse($model->issetCommands());
    }

    public function testSetCommands(): void
    {
        $model = BotCommandsPatch::new();

        $result = $model->setCommands([
            5 => new BotCommand('start'),
            9 => new BotCommand('help'),
        ]);

        self::assertSame($model, $result);
        self::assertSame('start', $model->getCommands()[0]->getName());
        self::assertSame('help', $model->getCommands()[1]->getName());
    }

    public function testSetCommandsOverLimit(): void
    {
        $this->expectException(MaxItemsException::class);

        BotCommandsPatch::make(self::makeCommands(33));
    }

    /**
     * @param positive-int $count
     * @return list<BotCommand>
     */
    private static function makeCommands(int $count): array
    {
        $commands = [];

        for ($i = 0; $i < $count; $i++) {
            $commands[] = new BotCommand("command$i");
        }

        return $commands;
    }
}
