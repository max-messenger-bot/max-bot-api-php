<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Request\CallbackButton;
use MaxMessenger\Bot\Model\Request\InlineKeyboardAttachmentRequest;
use MaxMessenger\Bot\Model\Request\LocationAttachmentRequest;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\NewMessageLink;
use MaxMessenger\Bot\Model\Request\StickerAttachmentRequest;
use MaxMessenger\Bot\Model\Request\StickerAttachmentRequestPayload;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageBody;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class NewMessageBodyTest extends Unit
{
    public function testAddAttachment(): void
    {
        $attachment = new StickerAttachmentRequest(new StickerAttachmentRequestPayload('sticker-code'));
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addAttachment($attachment));
        self::assertSame([$attachment], $body->getAttachments());
    }

    public function testAddAudioAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addAudioAttachment('audio-token'));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"audio","payload":{"token":"audio-token"}}]}',
            self::json($body),
        );
    }

    public function testAddContactAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addContactAttachment(200));
        $body->addContactAttachment(vcfInfo: "BEGIN:VCARD\nVERSION:3.0\nEND:VCARD");

        self::assertSame(
            '{"notify":true,"attachments":[{"type":"contact","payload":{"contact_id":200}},'
            . '{"type":"contact","payload":{"vcf_info":"BEGIN:VCARD\nVERSION:3.0\nEND:VCARD"}}]}',
            self::json($body),
        );
    }

    public function testAddFileAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addFileAttachment('file-token'));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"file","payload":{"token":"file-token"}}]}',
            self::json($body),
        );
    }

    public function testAddImageAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addImageAttachment('photo-token'));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"image","payload":{"token":"photo-token"}}]}',
            self::json($body),
        );
    }

    public function testAddInlineKeyboard(): void
    {
        $body = NewMessageBody::new();

        $keyboard = $body->addInlineKeyboard();
        $keyboard->addCallbackButton('Да', 'yes');

        $attachment = $body->getAttachments()[0] ?? null;
        self::assertInstanceOf(InlineKeyboardAttachmentRequest::class, $attachment);
        self::assertSame($keyboard, $attachment->getPayload());
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"inline_keyboard","payload":'
            . '{"buttons":[[{"type":"callback","text":"Да","payload":"yes"}]]}}]}',
            self::json($body),
        );
    }

    public function testAddInlineKeyboardAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addInlineKeyboardAttachment([[new CallbackButton('Да', 'yes')]]));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"inline_keyboard","payload":'
            . '{"buttons":[[{"type":"callback","text":"Да","payload":"yes"}]]}}]}',
            self::json($body),
        );
    }

    public function testAddLine(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addLine('Первая'));
        self::assertSame('Первая', $body->getText());

        $body->addLine('Вторая');
        self::assertSame("Первая\nВторая", $body->getText());
    }

    public function testAddLineToEmptyText(): void
    {
        $body = NewMessageBody::new()->setText('');

        $body->addLine('Первая');

        self::assertSame('Первая', $body->getText());
    }

    public function testAddLocationAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addLocationAttachment(55.75, 37.62));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"location","latitude":55.75,"longitude":37.62}]}',
            self::json($body),
        );
    }

    public function testAddShareAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addShareAttachment('https://example.test/'));
        $body->addShareAttachment('https://example.test/other');

        self::assertSame(
            '{"notify":true,"attachments":[{"type":"share","payload":{"url":"https://example.test/"}},'
            . '{"type":"share","payload":{"url":"https://example.test/other"}}]}',
            self::json($body),
        );
    }

    public function testAddStickerAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addStickerAttachment('sticker-code'));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"sticker","payload":{"code":"sticker-code"}}]}',
            self::json($body),
        );
    }

    public function testAddText(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addText('Привет'));
        $body->addText(', мир');

        self::assertSame('Привет, мир', $body->getText());
    }

    public function testAddTextOverLimitIsRejected(): void
    {
        $body = NewMessageBody::new(str_repeat('а', 4000));

        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "text" expects a string of up to 4000 characters, but a string of 4001 characters was passed.',
        );

        $body->addText('б');
    }

    public function testAddUrlImageAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addUrlImageAttachment('https://example.test/a.png'));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"image","payload":{"url":"https://example.test/a.png"}}]}',
            self::json($body),
        );
    }

    public function testAddVideoAttachment(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->addVideoAttachment('video-token'));
        self::assertSame(
            '{"notify":true,"attachments":[{"type":"video","payload":{"token":"video-token"}}]}',
            self::json($body),
        );
    }

    public function testAttachmentsAccessors(): void
    {
        $first = new LocationAttachmentRequest(1.5, 2.5);
        $second = new LocationAttachmentRequest(3.5, 4.5);
        $body = NewMessageBody::new();

        self::assertFalse($body->issetAttachments());
        self::assertNull($body->getAttachments());

        self::assertSame($body, $body->setAttachments(['a' => $first, 'b' => $second]));
        self::assertTrue($body->issetAttachments());
        self::assertSame([$first, $second], $body->getAttachments());

        self::assertSame($body, $body->unsetAttachments());
        self::assertFalse($body->issetAttachments());
        self::assertNull($body->getAttachments());
    }

    public function testConstructorWithAllFields(): void
    {
        $attachment = new LocationAttachmentRequest(1.5, 2.5);
        $link = new NewMessageLink('mid.1');

        $body = new NewMessageBody('Текст', [5 => $attachment], $link, false, TextFormat::Markdown);

        self::assertSame('Текст', $body->getText());
        self::assertSame([$attachment], $body->getAttachments());
        self::assertSame($link, $body->getLink());
        self::assertFalse($body->getNotify());
        self::assertSame(TextFormat::Markdown, $body->getFormat());
        self::assertSame(
            '{"text":"Текст","attachments":[{"type":"location","latitude":1.5,"longitude":2.5}],'
            . '"link":{"mid":"mid.1","type":"reply"},"notify":false,"format":"markdown"}',
            self::json($body),
        );
    }

    public function testConstructorWithTooLongTextIsRejected(): void
    {
        $this->expectException(MaxLengthException::class);

        new NewMessageBody(str_repeat('а', 4001));
    }

    public function testEmptyTextDoesNotSatisfyRequiredFields(): void
    {
        $body = NewMessageBody::new()->setText('');

        $this->expectException(RequiredOneFieldException::class);

        $body->jsonSerialize();
    }

    public function testFormatAccessors(): void
    {
        $body = NewMessageBody::new('Текст');

        self::assertFalse($body->issetFormat());
        self::assertNull($body->getFormat());

        self::assertSame($body, $body->setFormat(TextFormat::Html));
        self::assertTrue($body->issetFormat());
        self::assertSame(TextFormat::Html, $body->getFormat());
        self::assertSame('{"text":"Текст","notify":true,"format":"html"}', self::json($body));

        self::assertSame($body, $body->unsetFormat());
        self::assertFalse($body->issetFormat());
    }

    public function testLinkAccessors(): void
    {
        $link = new NewMessageLink('mid.1', MessageLinkType::Forward);
        $body = NewMessageBody::new();

        self::assertFalse($body->issetLink());
        self::assertNull($body->getLink());

        self::assertSame($body, $body->setLink($link));
        self::assertTrue($body->issetLink());
        self::assertSame($link, $body->getLink());
        self::assertSame('{"notify":true,"link":{"mid":"mid.1","type":"forward"}}', self::json($body));

        self::assertSame($body, $body->unsetLink());
        self::assertFalse($body->issetLink());
        self::assertNull($body->getLink());
    }

    public function testMake(): void
    {
        $link = new NewMessageLink('mid.1');

        $body = NewMessageBody::make('Текст', [], $link, false, TextFormat::Html);

        self::assertSame('Текст', $body->getText());
        self::assertSame([], $body->getAttachments());
        self::assertSame($link, $body->getLink());
        self::assertFalse($body->getNotify());
        self::assertSame(TextFormat::Html, $body->getFormat());
    }

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

    public function testRequiresTextAttachmentsOrLink(): void
    {
        $body = NewMessageBody::new();

        $this->expectException(RequiredOneFieldException::class);
        $this->expectExceptionMessage('At least one field of "text", "attachments", "link" cannot be null.');

        $body->jsonSerialize();
    }

    public function testSetForwardLink(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->setForwardLink('mid.1'));
        self::assertSame('mid.1', $body->getLink()?->getMid());
        self::assertSame(MessageLinkType::Forward, $body->getLink()?->getType());

        $body->setForwardLink(Message::newFromData(FakeMaxHttpClient::messageData()));
        self::assertSame('mid.out', $body->getLink()?->getMid());
        self::assertSame(MessageLinkType::Forward, $body->getLink()?->getType());

        $body->setForwardLink(MessageBody::newFromData(['mid' => 'mid.2', 'seq' => 1, 'text' => 'x']));
        self::assertSame('mid.2', $body->getLink()?->getMid());
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

    public function testSetReplyLink(): void
    {
        $body = NewMessageBody::new();

        self::assertSame($body, $body->setReplyLink('mid.1'));
        self::assertSame('mid.1', $body->getLink()?->getMid());
        self::assertSame(MessageLinkType::Reply, $body->getLink()?->getType());

        $body->setReplyLink(MessageBody::newFromData(['mid' => 'mid.2', 'seq' => 1, 'text' => 'x']));
        self::assertSame('mid.2', $body->getLink()?->getMid());
        self::assertSame(MessageLinkType::Reply, $body->getLink()?->getType());
    }

    public function testTextAccessors(): void
    {
        $body = NewMessageBody::new();

        self::assertFalse($body->issetText());
        self::assertNull($body->getText());

        self::assertSame($body, $body->setText('Текст'));
        self::assertTrue($body->issetText());
        self::assertSame('Текст', $body->getText());

        self::assertSame($body, $body->unsetText());
        self::assertFalse($body->issetText());
        self::assertNull($body->getText());
    }

    private static function json(NewMessageBody $body): string
    {
        return (string) json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
