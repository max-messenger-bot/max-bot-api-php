<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use ArrayObject;
use Codeception\Test\Unit;
use DateTimeImmutable;
use MaxMessenger\Bot\Exception\MaxBot\Event\EventException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\Event;
use MaxMessenger\Bot\MaxBot\Event\UnknownEvent;
use MaxMessenger\Bot\Model\Response\Update;
use RuntimeException;
use Throwable;

final class BaseEventTest extends Unit
{
    private MaxApiClient $apiClient;

    public function testApiClient(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertSame($this->apiClient, $event->apiClient);
    }

    public function testBreak(): void
    {
        $this->expectException(EventException::class);

        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $event->break();
    }

    public function testContinue(): void
    {
        $this->expectException(EventException::class);

        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $event->continue();
    }

    public function testCurrentHandlerListType(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertNull($event->currentHandlerListType);
    }

    public function testExceptionHandlerHandledFalse(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertFalse($event->isHandled);

                return true;
            },
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertFalse($event->isHandled);

                return false;
            },
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        $result = $event->handle(static function (BaseEvent $event): void {
            $event->isHandled = false;

            throw new RuntimeException('Test exception');
        });

        self::assertTrue($result);
        self::assertTrue($event->isHandled);
    }

    public function testExceptionHandlerHandledTrue(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertTrue($event->isHandled);
                $event->isHandled = true;

                return false;
            },
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertTrue($event->isHandled);
                $event->isHandled = true;

                return false;
            },
            static function (Throwable $exception, BaseEvent $event): void {
                self::assertTrue($event->isHandled);
                $event->isHandled = false;
            },
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        $result = $event->handle(static function (BaseEvent $event): void {
            $event->isHandled = true;

            throw new RuntimeException('Test exception');
        });

        self::assertTrue($result);
        self::assertTrue($event->isHandled);
    }

    public function testExceptionHandlerReturnsFalse(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertNull($event->isHandled);
                $event->isHandled = true;

                return false;
            },
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertNull($event->isHandled);
                $event->isHandled = true;

                return false;
            },
            static function (Throwable $exception, BaseEvent $event): void {
                self::assertNull($event->isHandled);
                $event->isHandled = true;
            },
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        $result = $event->handle(static function (): void {
            throw new RuntimeException('Test exception');
        });

        self::assertFalse($result);
        self::assertFalse($event->isHandled);
    }

    public function testExceptionHandlerReturnsTrue(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertNull($event->isHandled);
                $event->isHandled = false;

                return true;
            },
            static function (Throwable $exception, BaseEvent $event): bool {
                self::assertNull($event->isHandled);
                $event->isHandled = false;

                return false;
            },
            static function (Throwable $exception, BaseEvent $event): void {
                self::assertNull($event->isHandled);
                $event->isHandled = false;
            },
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        $result = $event->handle(static function (): void {
            throw new RuntimeException('Test exception');
        });

        self::assertTrue($result);
        self::assertTrue($event->isHandled);
    }

    public function testExceptionHandlerWithBreak(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (Throwable $exception, BaseEvent $event): void {
                self::assertNull($event->isHandled);
                Event::break();
            },
            static function (Throwable $exception, BaseEvent $event): void {
                self::assertNull($event->isHandled);
                Event::continue();
            },
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        $event->handle(static function (): void {
            throw new RuntimeException('Test exception');
        });
        self::assertTrue($event->isHandled);
    }

    public function testExceptionHandlerWithContinue(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (Throwable $exception, BaseEvent $event): void {
                self::assertNull($event->isHandled);
                Event::continue();
            },
            static function (Throwable $exception, BaseEvent $event): void {
                self::assertNull($event->isHandled);
                Event::continue();
            },
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        $event->handle(static function (): void {
            throw new RuntimeException('Test exception');
        });
        self::assertFalse($event->isHandled);
    }

    public function testExceptionHandlerWithExit(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (): void {
                Event::exit();
            },
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        try {
            $event->handle(static function (): void {
                throw new RuntimeException('Test exception');
            });

            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame('Test exception', $e->getMessage());
            self::assertNull($event->isHandled);
        }
    }

    public function testExceptionHandlerWithoutReturns(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [
            static function (): void {},
        ];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        try {
            $event->handle(static function (): void {
                throw new RuntimeException('Test exception');
            });

            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame('Test exception', $e->getMessage());
            self::assertNull($event->isHandled);
        }
    }

    public function testExceptionWithDefaultFalse(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        try {
            $event->handle(static function (): void {
                throw new RuntimeException('Test exception');
            }, false);

            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame('Test exception', $e->getMessage());
            self::assertFalse($event->isHandled);
        }
    }

    public function testExceptionWithDefaultTrue(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        try {
            $event->handle(static function (): void {
                throw new RuntimeException('Test exception');
            }, true);

            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame('Test exception', $e->getMessage());
            self::assertTrue($event->isHandled);
        }
    }

    public function testExceptionWithoutHandler(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $exceptionHandlers = [];

        $event = BaseEvent::new($update, $this->apiClient, $exceptionHandlers);

        try {
            $event->handle(static function (): void {
                throw new RuntimeException('Test exception');
            });

            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame('Test exception', $e->getMessage());
            self::assertNull($event->isHandled);
        }
    }

    public function testExit(): void
    {
        $this->expectException(EventException::class);

        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $event->exit();
    }

    public function testHandleDefaultFalse(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $result = $event->handle(static function (BaseEvent $_e): void {}, false);

        self::assertFalse($result);
        self::assertFalse($event->isHandled);
    }

    public function testHandleDefaultTrue(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $result = $event->handle(static function (BaseEvent $_e): void {}, true);

        self::assertTrue($result);
        self::assertTrue($event->isHandled);
    }

    public function testHandleReturnsFalse(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $result = $event->handle(static function (BaseEvent $_e): bool {
            return false;
        });

        self::assertFalse($result);
        self::assertFalse($event->isHandled);
    }

    public function testHandleReturnsNull(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $result = $event->handle(static function (BaseEvent $_e): void {});

        self::assertFalse($result);
        self::assertNull($event->isHandled);
    }

    public function testHandleReturnsTrue(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $result = $event->handle(static function (BaseEvent $_e): bool {
            return true;
        });

        self::assertTrue($result);
        self::assertTrue($event->isHandled);
    }

    public function testHandledIn(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertNull($event->handledIn);
    }

    public function testIsHandled(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertNull($event->isHandled);
    }

    public function testMarkHandled(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $event->markAsHandled();

        self::assertTrue($event->isHandled);
    }

    public function testMarkUnhandled(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);
        $event->markAsUnhandled();

        self::assertFalse($event->isHandled);
    }

    public function testNew(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertInstanceOf(UnknownEvent::class, $event);
    }

    public function testTimestamp(): void
    {
        $time = time();
        $timeMs = 500;
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => $time * 1000 + $timeMs]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        $eventTimestamp = $event->getTimestamp();
        self::assertInstanceOf(DateTimeImmutable::class, $eventTimestamp);
        self::assertSame($time, $eventTimestamp->getTimestamp());
        if (PHP_VERSION_ID >= 80400) {
            /** @psalm-suppress UndefinedMethod */
            self::assertSame($timeMs, $eventTimestamp->getMicrosecond());
        }
    }

    public function testTimestampRaw(): void
    {
        $timestamp = time() * 1000 + 500;
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => $timestamp]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertSame($timestamp, $event->getTimestampRaw());
    }

    public function testUpdate(): void
    {
        $timestamp = time() * 1000;
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => $timestamp]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertSame($update, $event->update);
    }

    public function testUser(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertNull($event->getUser());
    }

    public function testUserData(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);
        $userData = new ArrayObject(['key' => 'value']);

        $event = BaseEvent::new($update, $this->apiClient, [], $userData);

        self::assertSame($userData, $event->userData);
        self::assertSame('value', $event->userData['key']);
    }

    public function testUserDataModified(): void
    {
        $userData = new ArrayObject();
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, [], $userData);
        $event->userData['newKey'] = 'newValue';

        self::assertTrue($event->userData->offsetExists('newKey'));
        self::assertSame('newValue', $event->userData['newKey']);
    }

    public function testUserDataNotEmpty(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertInstanceOf(ArrayObject::class, $event->userData);
    }

    public function testUserId(): void
    {
        $update = Update::newFromData(['update_type' => 'unknown_type', 'timestamp' => time() * 1000]);

        $event = BaseEvent::new($update, $this->apiClient, []);

        self::assertNull($event->getUserId());
    }

    protected function _before(): void
    {
        $this->apiClient = new MaxApiClient('test-token');
    }
}
