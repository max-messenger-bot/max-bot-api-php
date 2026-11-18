<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\InlineKeyboardAttachment;
use MaxMessenger\Bot\Model\Response\Keyboard;

final class InlineKeyboardAttachmentTest extends Unit
{
    public function testGetPayload(): void
    {
        $attachment = InlineKeyboardAttachment::newFromData([
            'type' => 'inline_keyboard',
            'payload' => ['buttons' => [[['type' => 'callback', 'text' => 'Да', 'payload' => 'yes']]]],
        ]);

        self::assertSame(AttachmentType::InlineKeyboard, $attachment->getType());

        $keyboard = $attachment->getPayload();
        self::assertInstanceOf(Keyboard::class, $keyboard);
        self::assertSame('Да', $keyboard->getButtons()[0][0]->getText());
        self::assertSame($keyboard, $attachment->getPayload());
    }
}
