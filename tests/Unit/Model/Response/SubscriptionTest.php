<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Response\Subscription;

final class SubscriptionTest extends Unit
{
    public function testGetters(): void
    {
        $subscription = Subscription::newFromData([
            'url' => 'https://example.test/hook',
            'time' => 1_700_000_000_123,
            'update_types' => ['message_created', 'future_update', 'bot_started'],
            'version' => '0.0.10',
        ]);

        self::assertSame('https://example.test/hook', $subscription->getUrl());
        self::assertSame(1_700_000_000_123, $subscription->getTimeRaw());
        self::assertSame('2023-11-14 22:13:20.123 +00:00', $subscription->getTime()->format('Y-m-d H:i:s.v P'));
        self::assertSame([UpdateType::MessageCreated, UpdateType::BotStarted], $subscription->getUpdateTypes());
        self::assertSame(['message_created', 'future_update', 'bot_started'], $subscription->getUpdateTypesRaw());
        self::assertSame('0.0.10', $subscription->getVersion());
    }

    public function testWithoutOptionalFields(): void
    {
        $subscription = Subscription::newFromData(['url' => 'https://example.test/hook', 'time' => 1_700_000_000_000]);

        self::assertNull($subscription->getUpdateTypes());
        self::assertNull($subscription->getUpdateTypesRaw());
        self::assertNull($subscription->getVersion());
    }
}
