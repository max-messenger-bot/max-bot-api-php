<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\Model\Response\Update;

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
