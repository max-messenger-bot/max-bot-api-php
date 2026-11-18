<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use Error;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;
use RuntimeException;
use Throwable;

/**
 * Глобальная обработка ошибок обновлений.
 */
final class UpdateErrorHandlerTest extends Unit
{
    public function testDisablingHandlerRestoresExceptionPropagation(): void
    {
        $bot = new MaxBot('test-token');
        $handler = static function (Update $_update, Throwable $_exception): void {};
        self::assertNull($bot->getUpdateErrorHandler());
        self::assertSame($bot, $bot->setUpdateErrorHandler($handler));
        self::assertSame($handler, $bot->getUpdateErrorHandler());
        self::assertSame($bot, $bot->setUpdateErrorHandler(null));
        self::assertNull($bot->getUpdateErrorHandler());
        $exception = new RuntimeException('filter error');
        $bot->setUpdateFilter(static function () use ($exception): never {
            throw $exception;
        });

        try {
            $bot->handleUpdate(self::update());
            self::fail('Ожидалось исходное исключение');
        } catch (RuntimeException $caught) {
            self::assertSame($exception, $caught);
        }
    }

    public function testEventExceptionHandlersRunBeforeGlobalHandler(): void
    {
        $bot = new MaxBot('test-token');
        $globalCalls = 0;
        $bot->setUpdateErrorHandler(static function () use (&$globalCalls): void {
            ++$globalCalls;
        });
        $bot->onException(static fn(Throwable $_exception, BaseEvent $_event): bool => true);
        $bot->on('future_update', static function (): never {
            throw new RuntimeException('event error');
        });

        self::assertTrue($bot->handleUpdate(self::update()));
        self::assertSame(0, $globalCalls);
    }

    public function testFilterErrorPassesOriginalUpdateAndException(): void
    {
        $bot = new MaxBot('test-token');
        $update = self::update();
        $exception = new Error('filter error');
        $received = [];
        $bot->setUpdateFilter(static function () use ($exception): never {
            throw $exception;
        });
        $bot->setUpdateErrorHandler(static function (Update $update, Throwable $exception) use (&$received): void {
            $received = [$update, $exception];
        });

        self::assertFalse($bot->handleUpdate($update));
        self::assertSame([$update, $exception], $received);
    }

    public function testGlobalHandlerExceptionPropagatesWithoutRecursion(): void
    {
        $bot = new MaxBot('test-token');
        $exception = new RuntimeException('global handler error');
        $calls = 0;
        $bot->setUpdateFilter(static function (): never {
            throw new Error('filter error');
        });
        $bot->setUpdateErrorHandler(static function () use ($exception, &$calls): never {
            ++$calls;

            throw $exception;
        });

        try {
            $bot->handleUpdate(self::update());
            self::fail('Ожидалось исключение глобального обработчика');
        } catch (RuntimeException $caught) {
            self::assertSame($exception, $caught);
            self::assertSame(1, $calls);
        }
    }

    public function testPollingContinuesAfterHandledEventError(): void
    {
        $http = new FakeMaxHttpClient([
            'updates' => [
                ['update_type' => 'future_update', 'timestamp' => 1],
                ['update_type' => 'future_update', 'timestamp' => 2],
            ],
            'marker' => 42,
        ]);
        $bot = new MaxBot(new MaxApiClient(new MaxApiConfig('test-token'), httpClient: $http));
        $exception = new Error('first event failed');
        $handled = [];
        $errors = [];
        $bot->on('future_update', static function (BaseEvent $event) use ($exception, &$handled): void {
            $handled[] = $event->update->getTimestampRaw();
            if ($event->update->getTimestampRaw() === 1) {
                throw $exception;
            }
        });
        $bot->setUpdateErrorHandler(static function (Update $update, Throwable $error) use (&$errors): void {
            $errors[] = [$update->getTimestampRaw(), $error];
        });

        self::assertSame(42, $bot->handleFromServer());
        self::assertSame([1, 2], $handled);
        self::assertSame([[1, $exception]], $errors);
    }

    public function testRejectedUpdateDoesNotCallErrorHandler(): void
    {
        $bot = new MaxBot('test-token');
        $calls = 0;
        $bot->setUpdateFilter(static fn(): bool => false);
        $bot->setUpdateErrorHandler(static function () use (&$calls): void {
            ++$calls;
        });

        self::assertFalse($bot->handleUpdate(self::update()));
        self::assertSame(0, $calls);
    }

    private static function update(): Update
    {
        return Update::newFromData(['update_type' => 'future_update', 'timestamp' => 1]);
    }
}
