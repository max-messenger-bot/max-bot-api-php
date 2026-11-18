<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\GetSubscriptionsResult;

final class GetSubscriptionsResultTest extends Unit
{
    public function testEmptyList(): void
    {
        self::assertSame([], GetSubscriptionsResult::newFromData(['subscriptions' => []])->getSubscriptions());
    }

    public function testGetSubscriptions(): void
    {
        $result = GetSubscriptionsResult::newFromData([
            'subscriptions' => [
                ['url' => 'https://example.test/hook', 'time' => 1_700_000_000_000],
                ['url' => 'https://example.test/other', 'time' => 1_700_000_000_001],
            ],
        ]);

        $subscriptions = $result->getSubscriptions();

        self::assertCount(2, $subscriptions);
        self::assertSame('https://example.test/hook', $subscriptions[0]->getUrl());
        self::assertSame('https://example.test/other', $subscriptions[1]->getUrl());
        self::assertSame($subscriptions, $result->getSubscriptions());
    }
}
