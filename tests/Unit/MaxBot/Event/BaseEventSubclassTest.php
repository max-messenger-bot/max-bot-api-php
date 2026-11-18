<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use ArrayObject;
use Closure;
use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Event\ChatIdMissingException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;
use RuntimeException;
use Throwable;

/**
 * Собственное событие, унаследованное от {@see BaseEvent}.
 */
final class BaseEventSubclassTest extends Unit
{
    public function testConstructorKeepsArguments(): void
    {
        $update = Update::newFromData(['update_type' => 'custom', 'timestamp' => 1_700_000_000_000]);
        $apiClient = new MaxApiClient('test-token');
        $userData = new ArrayObject(['key' => 'value']);

        $event = $this->createEvent($update, $apiClient, [], $userData, chatId: 100);

        self::assertSame($update, $event->update);
        self::assertSame($apiClient, $event->apiClient);
        self::assertSame($userData, $event->userData);
        self::assertSame(1_700_000_000_000, $event->getTimestampRaw());
        self::assertSame(100, $event->requireChatId());
    }

    public function testExceptionHandlersArePassedThroughConstructor(): void
    {
        $exception = new RuntimeException('Ошибка обработчика');
        $seen = [];
        $exceptionHandler = static function (Throwable $e, BaseEvent $event) use (&$seen): bool {
            $seen[] = [$e, $event];

            return true;
        };
        $event = $this->createEvent(exceptionHandlers: [$exceptionHandler]);

        $handled = $event->handle(static function () use ($exception): never {
            throw $exception;
        });

        self::assertTrue($handled);
        self::assertSame([[$exception, $event]], $seen);
    }

    public function testRequireChatIdWithoutChat(): void
    {
        $event = $this->createEvent();

        $this->expectException(ChatIdMissingException::class);

        $event->requireChatId();
    }

    /**
     * @param list<Closure(Throwable $exception, BaseEvent $event): (bool|void)> $exceptionHandlers
     */
    private function createEvent(
        ?Update $update = null,
        ?MaxApiClient $apiClient = null,
        array $exceptionHandlers = [],
        ArrayObject $userData = new ArrayObject(),
        ?int $chatId = null,
    ): BaseEvent {
        $event = new class (
            $update ?? Update::newFromData(['update_type' => 'custom', 'timestamp' => 1_700_000_000_000]),
            $apiClient ?? new MaxApiClient('test-token'),
            $exceptionHandlers,
            $userData,
        ) extends BaseEvent {
            public ?int $chatId = null;

            /**
             * @param list<Closure(Throwable $exception, BaseEvent $event): (bool|void)> $exceptionHandlers
             * @psalm-capabilities write-this-props
             */
            public function __construct(
                Update $update,
                MaxApiClient $apiClient,
                array $exceptionHandlers,
                ArrayObject $userData,
            ) {
                parent::__construct($update, $apiClient, $exceptionHandlers, $userData);
            }

            public function getChatId(): ?int
            {
                return $this->chatId;
            }

            public function getUser(): ?User
            {
                return null;
            }

            public function getUserId(): ?int
            {
                return null;
            }
        };
        $event->chatId = $chatId;

        return $event;
    }
}
