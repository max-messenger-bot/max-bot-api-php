<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\PinMessageBody;

use function json_encode;

final class PinMessageBodyTest extends Unit
{
    public function testConstructor(): void
    {
        $body = new PinMessageBody('mid.1', false);

        self::assertTrue($body->issetMessageId());
        self::assertSame('mid.1', $body->getMessageId());
        self::assertFalse($body->getNotify());
    }

    public function testEmptyMessageIdIsRejected(): void
    {
        $this->expectException(MatchException::class);
        $this->expectExceptionMessage('Argument "messageId" has an invalid value.');

        /** @psalm-suppress InvalidArgument */
        new PinMessageBody('');
    }

    public function testInvalidMessageIdIsRejected(): void
    {
        $this->expectException(MatchException::class);
        $this->expectExceptionMessage('Argument "messageId" has an invalid value.');

        PinMessageBody::make('1234');
    }

    public function testJsonSerialize(): void
    {
        self::assertSame('{"message_id":"mid.1","notify":true}', json_encode(PinMessageBody::make('mid.1')));
        self::assertSame(
            '{"message_id":"mid.1","notify":false}',
            json_encode(PinMessageBody::make('mid.1', false)),
        );
    }

    public function testJsonSerializeWithoutMessageId(): void
    {
        $body = PinMessageBody::new();

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "message_id" field cannot be null.');

        $body->jsonSerialize();
    }

    public function testNewWithoutMessageId(): void
    {
        $body = PinMessageBody::new();

        self::assertFalse($body->issetMessageId());
        self::assertTrue($body->getNotify());
    }

    public function testSetters(): void
    {
        $body = PinMessageBody::new();

        self::assertSame($body, $body->setMessageId('mid.2'));
        self::assertSame($body, $body->setNotify(false));
        self::assertSame('mid.2', $body->getMessageId());
        self::assertFalse($body->getNotify());
    }
}
