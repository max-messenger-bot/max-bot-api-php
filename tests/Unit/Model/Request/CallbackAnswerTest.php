<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Model\Request\CallbackAnswer;
use MaxMessenger\Bot\Model\Request\NewMessageBody;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class CallbackAnswerTest extends Unit
{
    public function testConstructor(): void
    {
        $message = new NewMessageBody('Обновлено');
        $answer = new CallbackAnswer($message);

        self::assertTrue($answer->issetMessage());
        self::assertSame($message, $answer->getMessage());
    }

    public function testJsonSerialize(): void
    {
        $answer = CallbackAnswer::make(new NewMessageBody('Обновлено'));

        self::assertSame(
            '{"message":{"text":"Обновлено","notify":true}}',
            json_encode($answer, JSON_UNESCAPED_UNICODE),
        );
    }

    public function testJsonSerializeWithoutMessage(): void
    {
        $answer = CallbackAnswer::new();

        $this->expectException(RequiredOneFieldException::class);
        $this->expectExceptionMessage('At least one field of "message", "notification" cannot be null.');

        $answer->jsonSerialize();
    }

    public function testNewWithoutMessage(): void
    {
        $answer = CallbackAnswer::new();

        self::assertFalse($answer->issetMessage());
        self::assertNull($answer->getMessage());
        self::assertFalse($answer->issetNotification());
        self::assertNull($answer->getNotification());
    }

    public function testNotificationOnly(): void
    {
        $answer = CallbackAnswer::make(notification: 'Готово');

        self::assertFalse($answer->issetMessage());
        self::assertNull($answer->getMessage());
        self::assertTrue($answer->issetNotification());
        self::assertSame('Готово', $answer->getNotification());
        self::assertSame('{"notification":"Готово"}', json_encode($answer, JSON_UNESCAPED_UNICODE));
    }

    public function testSetMessage(): void
    {
        $answer = CallbackAnswer::new(new NewMessageBody('Старое'));
        $message = new NewMessageBody('Новое');

        $result = $answer->setMessage($message);

        self::assertSame($answer, $result);
        self::assertSame($message, $answer->getMessage());
    }

    public function testSetNotificationPreservesMessage(): void
    {
        $message = new NewMessageBody('Обновлено');
        $answer = CallbackAnswer::new($message);

        self::assertSame($answer, $answer->setNotification('Готово'));
        self::assertSame($message, $answer->getMessage());
        self::assertSame(
            '{"message":{"text":"Обновлено","notify":true},"notification":"Готово"}',
            json_encode($answer, JSON_UNESCAPED_UNICODE),
        );
    }

    public function testUnsetMessagePreservesNotification(): void
    {
        $answer = CallbackAnswer::new(new NewMessageBody('Обновлено'), 'Готово');

        self::assertSame($answer, $answer->unsetMessage());
        self::assertNull($answer->getMessage());
        self::assertSame('{"notification":"Готово"}', json_encode($answer, JSON_UNESCAPED_UNICODE));
    }

    public function testUnsetNotificationPreservesMessage(): void
    {
        $answer = CallbackAnswer::new(new NewMessageBody('Обновлено'), 'Готово');

        self::assertSame($answer, $answer->unsetNotification());
        self::assertFalse($answer->issetNotification());
        self::assertNull($answer->getNotification());
        self::assertSame(
            '{"message":{"text":"Обновлено","notify":true}}',
            json_encode($answer, JSON_UNESCAPED_UNICODE),
        );
    }
}
