<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Closure;
use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\Button;
use MaxMessenger\Bot\Model\Request\CallbackButton;
use MaxMessenger\Bot\Model\Request\ClipboardButton;
use MaxMessenger\Bot\Model\Request\LinkButton;
use MaxMessenger\Bot\Model\Request\MessageButton;
use MaxMessenger\Bot\Model\Request\OpenAppButton;
use MaxMessenger\Bot\Model\Request\RequestContactButton;
use MaxMessenger\Bot\Model\Request\RequestGeoLocationButton;

use function str_repeat;

/**
 * Общее поведение {@see Button} во всех типах кнопок.
 */
final class ButtonTest extends Unit
{
    /**
     * @return iterable<string, array{Closure(): Button, ButtonType}>
     */
    public static function buttons(): iterable
    {
        yield 'callback' => [static fn(): Button => new CallbackButton(), ButtonType::Callback];
        yield 'clipboard' => [static fn(): Button => new ClipboardButton(), ButtonType::Clipboard];
        yield 'link' => [static fn(): Button => new LinkButton(), ButtonType::Link];
        yield 'message' => [static fn(): Button => new MessageButton(), ButtonType::Message];
        yield 'open_app' => [static fn(): Button => new OpenAppButton(), ButtonType::OpenApp];
        yield 'request_contact' => [static fn(): Button => new RequestContactButton(), ButtonType::RequestContact];
        yield 'request_geo_location' => [
            static fn(): Button => new RequestGeoLocationButton(),
            ButtonType::RequestGeoLocation,
        ];
    }

    /**
     * @param Closure(): Button $factory
     * @dataProvider buttons
     */
    public function testGetType(Closure $factory, ButtonType $type): void
    {
        self::assertSame($type, $factory()->getType());
    }

    /**
     * @param Closure(): Button $factory
     * @dataProvider buttons
     */
    public function testSetText(Closure $factory): void
    {
        $button = $factory();

        self::assertFalse($button->issetText());
        self::assertSame($button, $button->setText('Кнопка'));
        self::assertTrue($button->issetText());
        self::assertSame('Кнопка', $button->getText());
    }

    /**
     * @param Closure(): Button $factory
     * @dataProvider buttons
     */
    public function testSetTextAcceptsMaxLength(Closure $factory): void
    {
        $text = str_repeat('я', 128);

        self::assertSame($text, $factory()->setText($text)->getText());
    }

    /**
     * @param Closure(): Button $factory
     * @dataProvider buttons
     */
    public function testSetTextRejectsEmptyText(Closure $factory): void
    {
        $button = $factory();

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "text" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        $button->setText('');
    }

    /**
     * @param Closure(): Button $factory
     * @dataProvider buttons
     */
    public function testSetTextRejectsTooLongText(Closure $factory): void
    {
        $button = $factory();

        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "text" expects a string of up to 128 characters, but a string of 129 characters was passed.',
        );

        $button->setText(str_repeat('я', 129));
    }

    /**
     * @param Closure(): Button $factory
     * @dataProvider buttons
     */
    public function testTextIsRequired(Closure $factory): void
    {
        $button = $factory();

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "text" field cannot be null.');

        $button->jsonSerialize();
    }
}
