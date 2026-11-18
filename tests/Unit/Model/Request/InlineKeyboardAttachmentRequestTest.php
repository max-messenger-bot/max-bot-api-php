<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinItemsException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Request\CallbackButton;
use MaxMessenger\Bot\Model\Request\InlineKeyboardAttachmentRequest;
use MaxMessenger\Bot\Model\Request\InlineKeyboardAttachmentRequestPayload;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class InlineKeyboardAttachmentRequestTest extends Unit
{
    public function testConstructorWithButtons(): void
    {
        $button = CallbackButton::make('Да', 'yes');
        $request = new InlineKeyboardAttachmentRequest([[$button]]);

        self::assertSame(AttachmentType::InlineKeyboard, $request->getType());
        self::assertTrue($request->issetPayload());
        self::assertSame([[$button]], $request->getPayload()->getButtons());
    }

    public function testConstructorWithInvalidButtonsIsRejected(): void
    {
        $this->expectException(MinItemsException::class);
        $this->expectExceptionMessage(
            'Argument "buttons[0]" expects a non-empty array, but an empty array was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        new InlineKeyboardAttachmentRequest([[]]);
    }

    public function testConstructorWithPayload(): void
    {
        $payload = InlineKeyboardAttachmentRequestPayload::make([[CallbackButton::make('Да', 'yes')]]);
        $request = new InlineKeyboardAttachmentRequest($payload);

        self::assertSame($payload, $request->getPayload());
    }

    public function testJsonSerialize(): void
    {
        $request = InlineKeyboardAttachmentRequest::make([[CallbackButton::make('Да', 'yes')]]);

        self::assertSame(
            '{"type":"inline_keyboard","payload":{"buttons":[[{"type":"callback","text":"Да","payload":"yes"}]]}}',
            json_encode($request, JSON_UNESCAPED_UNICODE),
        );
    }

    public function testMake(): void
    {
        $payload = InlineKeyboardAttachmentRequestPayload::make([[CallbackButton::make('Да', 'yes')]]);

        self::assertSame($payload, InlineKeyboardAttachmentRequest::make($payload)->getPayload());
    }

    public function testNew(): void
    {
        $request = InlineKeyboardAttachmentRequest::new();

        self::assertSame(AttachmentType::InlineKeyboard, $request->getType());
        self::assertFalse($request->issetPayload());

        $button = CallbackButton::make('Да', 'yes');
        self::assertSame([[$button]], InlineKeyboardAttachmentRequest::new([[$button]])->getPayload()->getButtons());
    }

    public function testPayloadIsRequired(): void
    {
        $request = new InlineKeyboardAttachmentRequest();

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $request->jsonSerialize();
    }

    public function testSetPayload(): void
    {
        $request = new InlineKeyboardAttachmentRequest();
        $payload = InlineKeyboardAttachmentRequestPayload::make([[CallbackButton::make('Да', 'yes')]]);

        self::assertSame($request, $request->setPayload($payload));
        self::assertTrue($request->issetPayload());
        self::assertSame($payload, $request->getPayload());
    }
}
