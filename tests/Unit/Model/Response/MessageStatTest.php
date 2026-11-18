<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\MessageStat;

final class MessageStatTest extends Unit
{
    public function testGetViews(): void
    {
        self::assertSame(42, MessageStat::newFromData(['views' => 42])->getViews());
        self::assertSame(0, MessageStat::newFromData(['views' => 0])->getViews());
    }
}
