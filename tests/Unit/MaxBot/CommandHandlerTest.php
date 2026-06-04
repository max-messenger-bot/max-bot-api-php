<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\CommandHandler;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Response\Update;

final class CommandHandlerTest extends Unit
{
    public function testCommandSeparatorSplitsPayload(): void
    {
        $event = $this->createEvent('/cmd привет мир');
        $handler = new CommandHandler();
        $handler->setCommandSeparator(' ');

        $handler->handle($event);

        self::assertSame('cmd', $event->userData['__command']);
        self::assertSame('привет мир', $event->userData['__payload']);
    }

    public function testCommandTooLongReturnsFalse(): void
    {
        $event = $this->createEvent('/abcd');
        $handler = (new CommandHandler())->setCommandMaxLength(3);

        self::assertFalse($handler->handle($event));
    }

    public function testGettersAndSetters(): void
    {
        $handler = new CommandHandler();

        self::assertSame(64, $handler->getCommandMaxLength());
        self::assertNull($handler->getCommandSeparator());

        self::assertSame($handler, $handler->setCommandMaxLength(10));
        self::assertSame($handler, $handler->setCommandSeparator(':'));

        self::assertSame(10, $handler->getCommandMaxLength());
        self::assertSame(':', $handler->getCommandSeparator());
    }

    public function testHandleMatchingCommand(): void
    {
        $event = $this->createEvent('/start');
        $handler = (new CommandHandler())->onCommand('start', static fn(MessageCreatedEvent $_e): bool => true);

        self::assertTrue($handler->handle($event));
        self::assertSame('start', $event->userData['__command']);
    }

    public function testHandleNoMatchingCommand(): void
    {
        $event = $this->createEvent('/other');
        $handler = (new CommandHandler())->onCommand('start', static fn(MessageCreatedEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandleNonCommandText(): void
    {
        $event = $this->createEvent('привет');
        $handler = (new CommandHandler())->onCommand('start', static fn(MessageCreatedEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testHandleNonDialogReturnsFalse(): void
    {
        $event = $this->createEvent('/start', 'chat');
        $handler = (new CommandHandler())->onCommand('start', static fn(MessageCreatedEvent $_e): bool => true);

        self::assertFalse($handler->handle($event));
    }

    public function testOnCommandsFallback(): void
    {
        $event = $this->createEvent('/anything');
        $handler = (new CommandHandler())->onCommands(static fn(MessageCreatedEvent $_e): bool => true);

        self::assertTrue($handler->handle($event));
    }

    private function createEvent(string $text, string $chatType = 'dialog'): MessageCreatedEvent
    {
        $update = Update::newFromData([
            'update_type' => 'message_created',
            'timestamp' => 1_700_000_000_000,
            'message' => [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => $chatType, 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => $text],
            ],
        ]);

        $event = BaseEvent::new($update, new MaxApiClient('test-token'), []);
        self::assertInstanceOf(MessageCreatedEvent::class, $event);

        return $event;
    }
}
