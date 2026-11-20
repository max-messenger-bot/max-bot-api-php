<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\Update;
use RuntimeException;
use Throwable;

final class UpdateFilterTest extends Unit
{
    public function testCustomFilterCanAcceptInvalidUpdate(): void
    {
        $bot = new MaxBot('test-token');
        $update = Update::newFromData(['update_type' => 'bot_started', 'timestamp' => 1]);
        $receivedUpdates = [];
        $handledUpdates = [];
        $filter = static function (Update $receivedUpdate) use (&$receivedUpdates): bool {
            $receivedUpdates[] = $receivedUpdate;

            return true;
        };

        self::assertFalse($update->isValid());
        self::assertSame($bot, $bot->setUpdateFilter($filter));
        self::assertSame($filter, $bot->getUpdateFilter());
        $bot->onPrepare(static function (BaseEvent $event) use (&$handledUpdates): bool {
            $handledUpdates[] = $event->update;

            return true;
        });

        self::assertTrue($bot->handleUpdate($update));
        self::assertSame([$update], $receivedUpdates);
        self::assertSame([$update], $handledUpdates);
    }

    public function testCustomFilterCanRejectValidUpdate(): void
    {
        $update = Update::newFromData(['update_type' => 'future_update', 'timestamp' => 1]);
        $bot = new MaxBot('test-token');
        $handledUpdates = [];
        $bot->onPrepare(static function (BaseEvent $event) use (&$handledUpdates): bool {
            $handledUpdates[] = $event->update;

            return true;
        });
        $bot->setUpdateFilter(static fn(Update $receivedUpdate): bool => $receivedUpdate !== $update);

        self::assertTrue($update->isValid());
        self::assertFalse($bot->handleUpdate($update));
        self::assertSame([], $handledUpdates);
    }

    public function testCustomFilterCanReplaceUpdate(): void
    {
        $bot = new MaxBot('test-token');
        $original = Update::newFromData(['update_type' => 'future_update', 'timestamp' => 1]);
        $replacement = BotAddedToChatUpdate::newFromData([
            'update_type' => 'bot_added',
            'timestamp' => 2,
            'chat_id' => 100,
            'is_channel' => false,
        ]);
        $receivedUpdates = [];
        $handledUpdates = [];
        $bot->setUpdateFilter(static function (Update $update) use ($replacement, &$receivedUpdates): Update {
            $receivedUpdates[] = $update;

            return $replacement;
        });
        $bot->onBotAddedToChat(static function (BotAddedToChatEvent $event) use (&$handledUpdates): bool {
            $handledUpdates[] = $event->update;

            return true;
        });

        self::assertTrue($bot->handleUpdate($original));
        self::assertSame([$original], $receivedUpdates);
        self::assertSame([$replacement], $handledUpdates);
    }

    public function testDefaultFilterChecksUpdateValidity(): void
    {
        $bot = new MaxBot('test-token');
        $validUpdate = Update::newFromData(['update_type' => 'future_update', 'timestamp' => 1]);
        $invalidUpdate = Update::newFromData(['update_type' => 'bot_started', 'timestamp' => 1]);
        $handledUpdates = [];
        $bot->onPrepare(static function (BaseEvent $event) use (&$handledUpdates): bool {
            $handledUpdates[] = $event->update;

            return true;
        });

        $filter = $bot->getUpdateFilter();
        self::assertNotNull($filter);
        self::assertTrue($filter($validUpdate));
        self::assertFalse($filter($invalidUpdate));
        self::assertFalse($bot->handleUpdate($invalidUpdate));
        self::assertTrue($bot->handleUpdate($validUpdate));
        self::assertSame([$validUpdate], $handledUpdates);
    }

    public function testDefaultFilterSkipsUpdatesWithoutMessage(): void
    {
        $bot = new MaxBot('test-token');
        $handledUpdates = [];
        $bot->onPrepare(static function (BaseEvent $event) use (&$handledUpdates): bool {
            $handledUpdates[] = $event->update;

            return true;
        });

        foreach (['message_created', 'message_edited', 'message_callback'] as $type) {
            $data = ['update_type' => $type, 'timestamp' => 1, 'callback' => []];
            $update = Update::newFromData($data);

            self::assertFalse($update->isValid());
            self::assertFalse($bot->handleUpdate($update));
            self::assertSame([], $handledUpdates);
        }

        foreach (['message_created', 'message_edited', 'message_callback'] as $type) {
            $update = Update::newFromData([
                'update_type' => $type,
                'timestamp' => 1,
                'callback' => [],
                'message' => [],
            ]);

            self::assertTrue($bot->handleUpdate($update));
        }

        self::assertCount(3, $handledUpdates);
    }

    public function testErrorHandlerReceivesReplacementUpdate(): void
    {
        $bot = new MaxBot('test-token');
        $original = Update::newFromData(['update_type' => 'future_update', 'timestamp' => 1]);
        $replacement = Update::newFromData(['update_type' => 'replacement_update', 'timestamp' => 2]);
        $exception = new RuntimeException('Failed to handle replacement');
        $receivedErrors = [];
        $bot->setUpdateFilter(static fn(): Update => $replacement);
        $bot->onPrepare(static function () use ($exception): never {
            throw $exception;
        });
        $bot->setUpdateErrorHandler(static function (Update $update, Throwable $error) use (&$receivedErrors): void {
            $receivedErrors[] = [$update, $error];
        });

        self::assertFalse($bot->handleUpdate($original));
        self::assertSame([[$replacement, $exception]], $receivedErrors);
    }

    public function testNullFilterDisablesValidation(): void
    {
        $bot = new MaxBot('test-token');
        $update = Update::newFromData(['update_type' => 'bot_started', 'timestamp' => 1]);
        $handledUpdates = [];
        $bot->onPrepare(static function (BaseEvent $event) use (&$handledUpdates): bool {
            $handledUpdates[] = $event->update;

            return true;
        });

        self::assertFalse($update->isValid());
        self::assertSame($bot, $bot->setUpdateFilter(null));
        self::assertNull($bot->getUpdateFilter());
        self::assertTrue($bot->handleUpdate($update));
        self::assertSame([$update], $handledUpdates);
    }
}
