<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\PhotoAttachmentRequestPayload;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class ChatPatchTest extends Unit
{
    public function testConstructor(): void
    {
        $icon = new PhotoAttachmentRequestPayload('https://example.test/icon.png');

        $patch = new ChatPatch($icon, 'Чат', 'Описание', 'mid.abc_1-2', false);

        self::assertSame($icon, $patch->getIcon());
        self::assertSame('Чат', $patch->getTitle());
        self::assertSame('Описание', $patch->getDescription());
        self::assertSame('mid.abc_1-2', $patch->getPin());
        self::assertFalse($patch->getNotify());
        self::assertTrue($patch->issetIcon());
        self::assertTrue($patch->issetTitle());
        self::assertTrue($patch->issetDescription());
        self::assertTrue($patch->issetPin());
    }

    public function testDescriptionTooLong(): void
    {
        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "description" expects a string of up to 16000 characters, but a string of 16001 characters'
            . ' was passed.',
        );

        ChatPatch::make(description: str_repeat('я', 16001));
    }

    public function testEmptyDescriptionRemovesDescription(): void
    {
        $patch = ChatPatch::make(description: '');

        self::assertTrue($patch->issetDescription());
        self::assertSame('', $patch->getDescription());
        self::assertSame('{"description":"","notify":true}', json_encode($patch));
    }

    public function testEmptyTitleIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "title" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        ChatPatch::make(title: '');
    }

    public function testInvalidPinIsRejected(): void
    {
        $this->expectException(MatchException::class);
        $this->expectExceptionMessage('Argument "pin" has an invalid value.');

        ChatPatch::make(pin: 'message.1');
    }

    public function testJsonSerialize(): void
    {
        $patch = ChatPatch::make(
            new PhotoAttachmentRequestPayload('https://example.test/icon.png'),
            'Чат',
            'Описание',
            'mid.1',
            false,
        );

        self::assertSame(
            '{"icon":{"url":"https://example.test/icon.png"},"title":"Чат","description":"Описание",'
            . '"pin":"mid.1","notify":false}',
            json_encode($patch, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
    }

    public function testNewWithoutArguments(): void
    {
        $patch = ChatPatch::new();

        self::assertFalse($patch->issetIcon());
        self::assertFalse($patch->issetTitle());
        self::assertFalse($patch->issetDescription());
        self::assertFalse($patch->issetPin());
        self::assertNull($patch->getIcon());
        self::assertNull($patch->getTitle());
        self::assertNull($patch->getDescription());
        self::assertNull($patch->getPin());
        self::assertTrue($patch->getNotify());
        self::assertSame('{"notify":true}', json_encode($patch));
    }

    public function testSetters(): void
    {
        $icon = new PhotoAttachmentRequestPayload(token: 'icon-token');
        $patch = ChatPatch::new();

        self::assertSame($patch, $patch->setIcon($icon));
        self::assertSame($patch, $patch->setTitle('Чат'));
        self::assertSame($patch, $patch->setDescription('Описание'));
        self::assertSame($patch, $patch->setPin('mid.1'));
        self::assertSame($patch, $patch->setNotify(false));
        self::assertSame($icon, $patch->getIcon());
        self::assertSame('Чат', $patch->getTitle());
        self::assertSame('Описание', $patch->getDescription());
        self::assertSame('mid.1', $patch->getPin());
        self::assertFalse($patch->getNotify());
    }

    public function testTitleAtMaxLength(): void
    {
        $title = str_repeat('я', 200);

        self::assertSame($title, ChatPatch::make(title: $title)->getTitle());
    }

    public function testTitleTooLong(): void
    {
        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "title" expects a string of up to 200 characters, but a string of 201 characters was passed.',
        );

        ChatPatch::make(title: str_repeat('я', 201));
    }

    public function testUnsetters(): void
    {
        $patch = ChatPatch::make(
            new PhotoAttachmentRequestPayload('https://example.test/icon.png'),
            'Чат',
            'Описание',
            'mid.1',
        );

        self::assertSame($patch, $patch->unsetIcon());
        self::assertSame($patch, $patch->unsetTitle());
        self::assertSame($patch, $patch->unsetDescription());
        self::assertSame($patch, $patch->unsetPin());
        self::assertFalse($patch->issetIcon());
        self::assertFalse($patch->issetTitle());
        self::assertFalse($patch->issetDescription());
        self::assertFalse($patch->issetPin());
        self::assertSame('{"notify":true}', json_encode($patch));
    }
}
