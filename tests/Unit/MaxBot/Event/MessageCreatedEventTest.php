<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Response\ContactAttachment;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;

use function hash_hmac;

final class MessageCreatedEventTest extends Unit
{
    public function testGetChatId(): void
    {
        self::assertSame(100, $this->createEvent()->getChatId());
    }

    public function testGetContactAttachment(): void
    {
        $event = $this->createEvent(attachments: [$this->createContactAttachment()]);
        $contact = $event->getContactAttachment();

        self::assertInstanceOf(ContactAttachment::class, $contact);
        self::assertSame($contact, $event->getContactAttachment());
        self::assertTrue($event->isContact());
        self::assertTrue($event->isSelfContact());
    }

    public function testGetContactAttachmentWithKeyboard(): void
    {
        $event = $this->createEvent(attachments: [
            ['type' => 'inline_keyboard', 'payload' => ['buttons' => [[
                ['type' => 'callback', 'text' => 'Кнопка', 'payload' => 'test'],
            ]]]],
            $this->createContactAttachment(),
        ]);

        self::assertInstanceOf(ContactAttachment::class, $event->getContactAttachment());
        self::assertTrue($event->isContact());
        self::assertTrue($event->isSelfContact());
    }

    public function testGetMessage(): void
    {
        $message = $this->createEvent()->getMessage();

        self::assertInstanceOf(Message::class, $message);
        self::assertSame('Привет', $message->getText());
    }

    public function testGetUser(): void
    {
        $user = $this->createEvent()->getUser();

        self::assertInstanceOf(User::class, $user);
        self::assertSame(200, $user->getUserId());
    }

    public function testGetUserId(): void
    {
        self::assertSame(200, $this->createEvent()->getUserId());
    }

    public function testGetUserLocale(): void
    {
        self::assertSame('ru-RU', $this->createEvent()->getUserLocale());
    }

    public function testIsChannel(): void
    {
        $event = $this->createEvent('channel');

        self::assertTrue($event->isChannel());
        self::assertFalse($event->isChat());
        self::assertFalse($event->isDialog());
    }

    public function testIsChat(): void
    {
        $event = $this->createEvent('chat');

        self::assertTrue($event->isChat());
        self::assertFalse($event->isDialog());
    }

    public function testIsContactWithoutContact(): void
    {
        $event = $this->createEvent();

        self::assertNull($event->getContactAttachment());
        self::assertFalse($event->isContact());
    }

    public function testIsDialog(): void
    {
        $event = $this->createEvent();

        self::assertTrue($event->isDialog());
        self::assertFalse($event->isChannel());
    }

    public function testIsSelfContactFalseWithInvalidHash(): void
    {
        $event = $this->createEvent(attachments: [$this->createContactAttachment(validHash: false)]);

        self::assertTrue($event->isContact());
        self::assertFalse($event->isSelfContact());
    }

    public function testIsSelfContactFalseWithoutContact(): void
    {
        self::assertFalse($this->createEvent()->isSelfContact());
    }

    public function testNoUserLocale(): void
    {
        self::assertNull($this->createEvent(userLocale: null)->getUserLocale());
    }

    private function createContactAttachment(bool $validHash = true): array
    {
        $vcf = "BEGIN:VCARD\nTEL:79990000000\nEND:VCARD\n";

        return [
            'type' => 'contact',
            'payload' => [
                'vcf_info' => $vcf,
                'hash' => $validHash ? hash_hmac('sha256', $vcf, 'test-token') : 'invalid',
            ],
        ];
    }

    private function createEvent(
        string $chatType = 'dialog',
        ?string $userLocale = 'ru-RU',
        bool $withMessage = true,
        ?array $attachments = null,
    ): MessageCreatedEvent {
        $message = [
            'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
            'recipient' => ['chat_id' => 100, 'chat_type' => $chatType, 'user_id' => 200],
            'timestamp' => 1_700_000_000_000,
            'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
        ];
        if ($attachments !== null) {
            $message['body']['attachments'] = $attachments;
        }

        $data = [
            'update_type' => 'message_created',
            'timestamp' => 1_700_000_000_000,
        ];
        if ($withMessage) {
            $data['message'] = $message;
        }
        if ($userLocale !== null) {
            $data['user_locale'] = $userLocale;
        }

        $event = BaseEvent::new(Update::newFromData($data), new MaxApiClient('test-token'), []);
        self::assertInstanceOf(MessageCreatedEvent::class, $event);

        return $event;
    }
}
