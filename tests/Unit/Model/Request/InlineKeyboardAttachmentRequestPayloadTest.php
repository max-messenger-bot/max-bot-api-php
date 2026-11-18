<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\KeyboardException;
use MaxMessenger\Bot\Exception\Validation\MaxItemsException;
use MaxMessenger\Bot\Exception\Validation\MinItemsException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\Button;
use MaxMessenger\Bot\Model\Request\CallbackButton;
use MaxMessenger\Bot\Model\Request\InlineKeyboardAttachmentRequestPayload;
use MaxMessenger\Bot\Model\Request\MessageButton;

use function array_fill;
use function json_encode;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class InlineKeyboardAttachmentRequestPayloadTest extends Unit
{
    public function testAddButtonAfterSetButtonsAppendsToLastRow(): void
    {
        $first = MessageButton::make('1');
        $second = MessageButton::make('2');
        $third = MessageButton::make('3');
        $payload = InlineKeyboardAttachmentRequestPayload::make([[$first], [$second]]);

        $payload->addButton($third);

        self::assertSame([[$first], [$second, $third]], $payload->getButtons());
    }

    public function testAddButtonAppendsToCurrentRow(): void
    {
        $first = MessageButton::make('1');
        $second = MessageButton::make('2');
        $payload = new InlineKeyboardAttachmentRequestPayload();

        $payload->addButton($first);
        $payload->addButton($second);

        self::assertTrue($payload->issetButtons());
        self::assertSame([[$first, $second]], $payload->getButtons());
    }

    public function testAddButtonRejectsButtonOverRowLimit(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();
        for ($i = 0; $i < InlineKeyboardAttachmentRequestPayload::MAX_BUTTONS_IN_ROW; ++$i) {
            $payload->addMessageButton('Кнопка');
        }

        $this->expectException(KeyboardException::class);
        $this->expectExceptionMessage('Cannot add more buttons to this row.');

        $payload->addMessageButton('Лишняя');
    }

    public function testAddButtonRejectsRowOverRowsLimit(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();
        $payload->addMessageButton('Кнопка');
        for ($i = 1; $i < InlineKeyboardAttachmentRequestPayload::MAX_ROWS; ++$i) {
            $payload->newRow()->addMessageButton('Кнопка');
        }

        self::assertCount(InlineKeyboardAttachmentRequestPayload::MAX_ROWS, $payload->getButtons());

        $this->expectException(KeyboardException::class);
        $this->expectExceptionMessage('Cannot add more rows.');

        $payload->newRow()->addMessageButton('Лишняя');
    }

    public function testAddHelpersBuildButtons(): void
    {
        $payload = (new InlineKeyboardAttachmentRequestPayload())
            ->addCallbackButton('Да', 'yes')
            ->addCallbackButton('Купить', ['id' => 7])
            ->addClipboardButton('Код', 'PROMO')
            ->newRow()
            ->addLinkButton('Сайт', 'https://example.test')
            ->addMessageButton('Привет')
            ->addOpenAppButton('Открыть', 'my_bot', 123_456, 'start')
            ->newRow()
            ->addRequestContactButton('Контакт')
            ->addRequestGeoLocationButton('Где я')
            ->addRequestGeoLocationButton('Сразу', true);

        self::assertSame(
            '{"buttons":['
            . '[{"type":"callback","text":"Да","payload":"yes"},'
            . '{"type":"callback","text":"Купить","payload":"{\"id\":7}"},'
            . '{"type":"clipboard","text":"Код","payload":"PROMO"}],'
            . '[{"type":"link","text":"Сайт","url":"https://example.test"},'
            . '{"type":"message","text":"Привет"},'
            . '{"type":"open_app","text":"Открыть","web_app":"my_bot","contact_id":123456,"payload":"start"}],'
            . '[{"type":"request_contact","text":"Контакт"},'
            . '{"type":"request_geo_location","text":"Где я","quick":false},'
            . '{"type":"request_geo_location","text":"Сразу","quick":true}]'
            . ']}',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
    }

    public function testAddOpenAppButtonWithDefaults(): void
    {
        $payload = (new InlineKeyboardAttachmentRequestPayload())->addOpenAppButton('Открыть', 'my_bot');

        self::assertSame(
            '{"buttons":[[{"type":"open_app","text":"Открыть","web_app":"my_bot"}]]}',
            json_encode($payload, JSON_UNESCAPED_UNICODE),
        );
    }

    public function testButtonsAreRequired(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();

        self::assertFalse($payload->issetButtons());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "buttons" field cannot be null.');

        $payload->jsonSerialize();
    }

    public function testConstructorWithButtons(): void
    {
        $button = MessageButton::make('1');

        self::assertSame([[$button]], (new InlineKeyboardAttachmentRequestPayload([[$button]]))->getButtons());
    }

    public function testMake(): void
    {
        $button = MessageButton::make('1');

        self::assertSame([[$button]], InlineKeyboardAttachmentRequestPayload::make([[$button]])->getButtons());
    }

    public function testNew(): void
    {
        $button = MessageButton::make('1');

        self::assertFalse(InlineKeyboardAttachmentRequestPayload::new()->issetButtons());
        self::assertSame([[$button]], InlineKeyboardAttachmentRequestPayload::new([[$button]])->getButtons());
    }

    public function testNewRowBeforeFirstButtonDoesNotAddEmptyRow(): void
    {
        $first = MessageButton::make('1');
        $second = MessageButton::make('2');
        $payload = new InlineKeyboardAttachmentRequestPayload();

        $payload->newRow()->addButton($first);
        $payload->addButton($second);

        self::assertSame([[$first, $second]], $payload->getButtons());
    }

    public function testNewRowStartsNextRow(): void
    {
        $first = MessageButton::make('1');
        $second = MessageButton::make('2');
        $third = MessageButton::make('3');
        $payload = new InlineKeyboardAttachmentRequestPayload();

        $payload->addButton($first);
        self::assertSame($payload, $payload->newRow());
        $payload->addButton($second);
        $payload->addButton($third);

        self::assertSame([[$first], [$second, $third]], $payload->getButtons());
    }

    public function testSetButtons(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();
        $button = MessageButton::make('1');

        self::assertSame($payload, $payload->setButtons([[$button]]));
        self::assertTrue($payload->issetButtons());
        self::assertSame([[$button]], $payload->getButtons());
    }

    public function testSetButtonsAcceptsLimits(): void
    {
        $row = array_fill(0, InlineKeyboardAttachmentRequestPayload::MAX_BUTTONS_IN_ROW, MessageButton::make('1'));
        $buttons = array_fill(0, InlineKeyboardAttachmentRequestPayload::MAX_ROWS, $row);

        self::assertSame($buttons, (new InlineKeyboardAttachmentRequestPayload($buttons))->getButtons());
    }

    public function testSetButtonsReindexesRowsAndButtons(): void
    {
        $first = MessageButton::make('1');
        $second = CallbackButton::make('2', 'two');

        $payload = InlineKeyboardAttachmentRequestPayload::make([5 => [3 => $first, 'b' => $second]]);

        self::assertSame([[$first, $second]], $payload->getButtons());
    }

    public function testSetButtonsRejectsEmptyButtons(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();

        $this->expectException(MinItemsException::class);
        $this->expectExceptionMessage('Argument "buttons" expects a non-empty array, but an empty array was passed.');

        /** @psalm-suppress InvalidArgument */
        $payload->setButtons([]);
    }

    public function testSetButtonsRejectsEmptyRow(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();

        $this->expectException(MinItemsException::class);
        $this->expectExceptionMessage(
            'Argument "buttons[1]" expects a non-empty array, but an empty array was passed.',
        );

        /** @psalm-suppress ArgumentTypeCoercion */
        $payload->setButtons([[MessageButton::make('1')], []]);
    }

    public function testSetButtonsRejectsTooManyButtonsInRow(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();
        /** @var non-empty-list<Button> $row */
        $row = array_fill(0, InlineKeyboardAttachmentRequestPayload::MAX_BUTTONS_IN_ROW + 1, MessageButton::make('1'));

        $this->expectException(MaxItemsException::class);
        $this->expectExceptionMessage(
            'Argument "buttons[0]" expects an array of up to 7 items, but an array of 8 items was passed.',
        );

        $payload->setButtons([$row]);
    }

    public function testSetButtonsRejectsTooManyRows(): void
    {
        $payload = new InlineKeyboardAttachmentRequestPayload();
        /** @var non-empty-list<non-empty-list<Button>> $buttons */
        $buttons = array_fill(0, InlineKeyboardAttachmentRequestPayload::MAX_ROWS + 1, [MessageButton::make('1')]);

        $this->expectException(MaxItemsException::class);
        $this->expectExceptionMessage(
            'Argument "buttons" expects an array of up to 30 items, but an array of 31 items was passed.',
        );

        $payload->setButtons($buttons);
    }
}
