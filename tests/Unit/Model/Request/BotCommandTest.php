<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\BotCommand;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_UNICODE;

final class BotCommandTest extends Unit
{
    public function testConstructor(): void
    {
        $command = new BotCommand('start', 'Начать работу');

        self::assertTrue($command->issetName());
        self::assertTrue($command->issetDescription());
        self::assertSame('start', $command->getName());
        self::assertSame('Начать работу', $command->getDescription());
    }

    public function testDescriptionAtMaxLength(): void
    {
        $description = str_repeat('я', 128);

        self::assertSame($description, BotCommand::make('start', $description)->getDescription());
    }

    public function testDescriptionTooLong(): void
    {
        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "description" expects a string of up to 128 characters, but a string of 129 characters was '
            . 'passed.',
        );

        BotCommand::make('start', str_repeat('я', 129));
    }

    public function testEmptyDescriptionIsRejected(): void
    {
        $command = BotCommand::make('start');

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "description" expects a non-empty string, but an empty string was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        $command->setDescription('');
    }

    public function testEmptyNameIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "name" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        new BotCommand('');
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"name":"start","description":"Начать работу"}',
            json_encode(BotCommand::make('start', 'Начать работу'), JSON_UNESCAPED_UNICODE),
        );
        self::assertSame('{"name":"help"}', json_encode(BotCommand::make('help')));
    }

    public function testJsonSerializeWithoutName(): void
    {
        $command = BotCommand::new(description: 'Без названия');

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "name" field cannot be null.');

        $command->jsonSerialize();
    }

    public function testNameTooLong(): void
    {
        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "name" expects a string of up to 64 characters, but a string of 65 characters was passed.',
        );

        BotCommand::make(str_repeat('я', 65));
    }

    public function testNewWithoutArguments(): void
    {
        $command = BotCommand::new();

        self::assertFalse($command->issetName());
        self::assertFalse($command->issetDescription());
        self::assertNull($command->getDescription());
    }

    public function testSetters(): void
    {
        $command = BotCommand::new();

        self::assertSame($command, $command->setName('help'));
        self::assertSame($command, $command->setDescription('Справка'));
        self::assertSame('help', $command->getName());
        self::assertSame('Справка', $command->getDescription());
    }

    public function testUnsetDescription(): void
    {
        $command = BotCommand::make('start', 'Начать работу');

        $result = $command->unsetDescription();

        self::assertSame($command, $result);
        self::assertFalse($command->issetDescription());
        self::assertNull($command->getDescription());
        self::assertSame('{"name":"start"}', json_encode($command));
    }
}
