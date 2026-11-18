<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiLogicException;
use MaxMessenger\Bot\Exception\MaxBot\Event\EventException;

final class EventExceptionTest extends Unit
{
    /**
     * @return iterable<string, array{bool|null}>
     */
    public static function statuses(): iterable
    {
        yield 'обработано' => [true];
        yield 'не обработано' => [false];
        yield 'без изменения статуса' => [null];
    }

    public function testDefaultStatusIsNull(): void
    {
        self::assertNull((new EventException())->isHandled);
    }

    public function testExtendsMaxApiLogicException(): void
    {
        self::assertInstanceOf(MaxApiLogicException::class, new EventException());
    }

    public function testMessage(): void
    {
        $exception = new EventException();

        self::assertSame('Internal Event Exception', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    /**
     * @dataProvider statuses
     */
    public function testStatus(?bool $isHandled): void
    {
        self::assertSame($isHandled, (new EventException($isHandled))->isHandled);
    }
}
