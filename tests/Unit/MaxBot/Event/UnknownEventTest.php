<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\UnknownEvent;
use MaxMessenger\Bot\Model\Response\Update;

final class UnknownEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertNull($this->createEvent()->getChatId());
    }

    public function testGetUser(): void
    {
        self::assertNull($this->createEvent()->getUser());
    }

    public function testGetUserId(): void
    {
        self::assertNull($this->createEvent()->getUserId());
    }

    private function createEvent(): UnknownEvent
    {
        $data = [
            'update_type' => 'something_unknown',
            'timestamp' => 1_700_000_000_000,
        ];

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(UnknownEvent::class, $event);

        return $event;
    }
}
