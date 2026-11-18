<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\ButtonType;
use MaxMessenger\Bot\Model\Request\LinkButton;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class LinkButtonTest extends Unit
{
    public function testConstructor(): void
    {
        $button = new LinkButton('Сайт', 'https://example.test');

        self::assertSame(ButtonType::Link, $button->getType());
        self::assertSame('Сайт', $button->getText());
        self::assertSame('https://example.test', $button->getUrl());
        self::assertTrue($button->issetUrl());
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            '{"type":"link","text":"Сайт","url":"https://example.test"}',
            json_encode(
                LinkButton::make('Сайт', 'https://example.test'),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ),
        );
    }

    public function testMake(): void
    {
        $button = LinkButton::make('Сайт', 'https://example.test');

        self::assertSame('Сайт', $button->getText());
        self::assertSame('https://example.test', $button->getUrl());
    }

    public function testNew(): void
    {
        $button = LinkButton::new();

        self::assertFalse($button->issetText());
        self::assertFalse($button->issetUrl());
        self::assertSame('https://example.test', LinkButton::new('Сайт', 'https://example.test')->getUrl());
    }

    public function testSetUrl(): void
    {
        $button = new LinkButton('Сайт');
        $longUrl = 'https://example.test/' . str_repeat('a', 2027);

        self::assertSame($button, $button->setUrl('a'));
        self::assertSame('a', $button->getUrl());
        self::assertSame($longUrl, $button->setUrl($longUrl)->getUrl());
    }

    public function testSetUrlRejectsTooLongUrl(): void
    {
        $button = new LinkButton('Сайт');

        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "url" expects a string of up to 2048 characters, but a string of 2049 characters was passed.',
        );

        $button->setUrl('https://example.test/' . str_repeat('a', 2028));
    }

    public function testSetUrlRejectsTooShortUrl(): void
    {
        $button = new LinkButton('Сайт');

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "url" expects a non-empty string, but an empty string was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        $button->setUrl('');
    }

    public function testUrlIsRequired(): void
    {
        $button = new LinkButton('Сайт');

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "url" field cannot be null.');

        $button->jsonSerialize();
    }
}
