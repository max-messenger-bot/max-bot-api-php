<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception;

use Codeception\Test\Unit;
use LogicException;
use MaxMessenger\Bot\Exception\ActionProhibited;
use MaxMessenger\Bot\Exception\MaxApiLogicException;

final class ActionProhibitedTest extends Unit
{
    public function testExtendsMaxApiLogicException(): void
    {
        $exception = new ActionProhibited();

        self::assertInstanceOf(MaxApiLogicException::class, $exception);
        self::assertInstanceOf(LogicException::class, $exception);
    }

    public function testMessage(): void
    {
        $exception = new ActionProhibited();

        self::assertSame('Action prohibited.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
