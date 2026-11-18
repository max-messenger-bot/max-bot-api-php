<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\AudioAttachment;
use MaxMessenger\Bot\Model\Response\MediaAttachmentPayload;

final class AudioAttachmentTest extends Unit
{
    public function testGetters(): void
    {
        $attachment = AudioAttachment::newFromData([
            'type' => 'audio',
            'payload' => ['url' => 'https://example.test/audio.mp3', 'id' => 42, 'token' => 'audio-token'],
        ]);

        self::assertSame(AttachmentType::Audio, $attachment->getType());

        $payload = $attachment->getPayload();
        self::assertInstanceOf(MediaAttachmentPayload::class, $payload);
        self::assertSame(42, $payload->getId());
        self::assertSame('audio-token', $payload->getToken());
        self::assertSame('https://example.test/audio.mp3', $payload->getUrl());
        self::assertSame($payload, $attachment->getPayload());
    }
}
