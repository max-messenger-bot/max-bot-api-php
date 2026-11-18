<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\HttpClient\Exception\HttpRequest\JsonEncodeException;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\CallbackButton;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_UNICODE;

final class CallbackButtonTest extends Unit
{
    public function testArrayPayloadIsEncodedToJson(): void
    {
        $button = new CallbackButton('Купить', ['action' => 'buy', 'id' => 7, 'name' => 'Чай']);

        self::assertSame('{"action":"buy","id":7,"name":"Чай"}', $button->getPayload());
    }

    public function testArrayPayloadThatCannotBeEncodedIsRejected(): void
    {
        $payload = ['text' => "\xB1\x31"];

        try {
            new CallbackButton('Купить', $payload);
            self::fail('Ожидалась ошибка кодирования JSON');
        } catch (JsonEncodeException $e) {
            self::assertSame($payload, $e->getData());
            self::assertInstanceOf(JsonException::class, $e->getPrevious());
        }
    }

    public function testArrayPayloadTooLongIsRejected(): void
    {
        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "payload" expects a string of up to 1024 characters, but a string of 1033 characters was passed.',
        );

        new CallbackButton('Купить', ['id' => str_repeat('a', 1024)]);
    }

    public function testConstructor(): void
    {
        $button = new CallbackButton('Да', 'yes');

        self::assertSame(ButtonType::Callback, $button->getType());
        self::assertSame('Да', $button->getText());
        self::assertSame('yes', $button->getPayload());
        self::assertTrue($button->issetText());
        self::assertTrue($button->issetPayload());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"type":"callback","text":"Да","payload":"yes"}',
            json_encode(CallbackButton::make('Да', 'yes'), JSON_UNESCAPED_UNICODE),
        );
    }

    public function testMake(): void
    {
        $button = CallbackButton::make('Да', 'yes');

        self::assertSame('Да', $button->getText());
        self::assertSame('yes', $button->getPayload());
    }

    public function testNew(): void
    {
        $button = CallbackButton::new();

        self::assertFalse($button->issetText());
        self::assertFalse($button->issetPayload());
        self::assertSame('yes', CallbackButton::new('Да', 'yes')->getPayload());
    }

    public function testPayloadIsRequired(): void
    {
        $button = new CallbackButton('Да');

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "payload" field cannot be null.');

        $button->jsonSerialize();
    }

    public function testSetPayload(): void
    {
        $button = new CallbackButton('Да');

        self::assertSame($button, $button->setPayload('yes'));
        self::assertSame('yes', $button->getPayload());
        self::assertSame(str_repeat('a', 1024), $button->setPayload(str_repeat('a', 1024))->getPayload());
    }

    public function testSetPayloadRejectsEmptyPayload(): void
    {
        $button = new CallbackButton('Да');

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "payload" expects a non-empty string, but an empty string was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        $button->setPayload('');
    }

    public function testSetPayloadRejectsTooLongPayload(): void
    {
        $button = new CallbackButton('Да');

        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "payload" expects a string of up to 1024 characters, but a string of 1025 characters was passed.',
        );

        $button->setPayload(str_repeat('я', 1025));
    }
}
