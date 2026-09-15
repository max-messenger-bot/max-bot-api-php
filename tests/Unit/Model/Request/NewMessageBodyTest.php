<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Request\NewMessageBody;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class NewMessageBodyTest extends Unit
{
    public function testNotifyByDefault(): void
    {
        $body = NewMessageBody::new()->setText('Обычное сообщение');

        self::assertTrue($body->getNotify());
        self::assertSame(
            '{"notify":true,"text":"Обычное сообщение"}',
            json_encode($body->jsonSerialize(), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testNotifyFromConstructor(): void
    {
        self::assertFalse((new NewMessageBody('Тихое сообщение', notify: false))->getNotify());
        self::assertFalse(NewMessageBody::make('Тихое сообщение', notify: false)->getNotify());
        self::assertFalse(NewMessageBody::new(notify: false)->getNotify());
    }

    public function testSetNotify(): void
    {
        $body = NewMessageBody::new()->setText('Тихое сообщение')->setNotify(false);

        self::assertFalse($body->getNotify());
        self::assertSame(
            '{"notify":false,"text":"Тихое сообщение"}',
            json_encode($body->jsonSerialize(), JSON_UNESCAPED_UNICODE),
        );
    }
}
