<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxBot\HandlerListType;

final class HandlerListTypeTest extends Unit
{
    public function testCases(): void
    {
        self::assertCount(6, HandlerListType::cases());

        self::assertContains(HandlerListType::Prepare, HandlerListType::cases());
        self::assertContains(HandlerListType::Event, HandlerListType::cases());
        self::assertContains(HandlerListType::Typed, HandlerListType::cases());
        self::assertContains(HandlerListType::Fallback, HandlerListType::cases());
        self::assertContains(HandlerListType::Final, HandlerListType::cases());
        self::assertContains(HandlerListType::Custom, HandlerListType::cases());
    }
}
