<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\Callback;
use MaxMessenger\Bot\Model\Response\User;

final class CallbackTest extends Unit
{
    public function testGetters(): void
    {
        $callback = Callback::newFromData([
            'timestamp' => 1_700_000_000_000,
            'callback_id' => 'cb.1',
            'payload' => '{"action":"start"}',
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ]);

        self::assertSame('cb.1', $callback->getCallbackId());
        self::assertSame('{"action":"start"}', $callback->getPayload());
        self::assertSame(1_700_000_000_000, $callback->getTimestampRaw());
        self::assertSame('2023-11-14 22:13:20', $callback->getTimestamp()->format('Y-m-d H:i:s'));

        $user = $callback->getUser();
        self::assertInstanceOf(User::class, $user);
        self::assertSame(200, $user->getUserId());
        self::assertSame($user, $callback->getUser());
    }
}
