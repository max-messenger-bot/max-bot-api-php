<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Request\ActionRequestBody;

use function json_encode;

final class ActionRequestBodyTest extends Unit
{
    public function testConstructor(): void
    {
        $body = new ActionRequestBody(SenderAction::TypingOn);

        self::assertTrue($body->issetAction());
        self::assertSame(SenderAction::TypingOn, $body->getAction());
    }

    public function testJsonSerialize(): void
    {
        $body = ActionRequestBody::make(SenderAction::MarkSeen);

        self::assertSame('{"action":"mark_seen"}', json_encode($body));
    }

    public function testJsonSerializeWithoutAction(): void
    {
        $body = ActionRequestBody::new();

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "action" field cannot be null.');

        $body->jsonSerialize();
    }

    public function testNewWithoutAction(): void
    {
        self::assertFalse(ActionRequestBody::new()->issetAction());
    }

    public function testSetAction(): void
    {
        $body = ActionRequestBody::new(SenderAction::TypingOn);

        $result = $body->setAction(SenderAction::SendingFile);

        self::assertSame($body, $result);
        self::assertSame(SenderAction::SendingFile, $body->getAction());
        self::assertSame('{"action":"sending_file"}', json_encode($body));
    }
}
