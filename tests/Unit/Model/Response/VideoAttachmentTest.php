<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Response\MediaAttachmentPayload;
use MaxMessenger\Bot\Model\Response\VideoAttachment;
use MaxMessenger\Bot\Model\Response\VideoThumbnail;

final class VideoAttachmentTest extends Unit
{
    public function testGetters(): void
    {
        $attachment = VideoAttachment::newFromData([
            'type' => 'video',
            'payload' => ['url' => 'https://example.test/video.mp4', 'id' => 42, 'token' => 'video-token'],
            'thumbnail' => ['url' => 'https://example.test/thumb.jpg'],
            'width' => 1920,
            'height' => 1080,
            'duration' => 125,
        ]);

        self::assertSame(AttachmentType::Video, $attachment->getType());
        self::assertSame(1920, $attachment->getWidth());
        self::assertSame(1080, $attachment->getHeight());
        self::assertSame(125, $attachment->getDuration());

        $payload = $attachment->getPayload();
        self::assertInstanceOf(MediaAttachmentPayload::class, $payload);
        self::assertSame(42, $payload->getId());
        self::assertSame($payload, $attachment->getPayload());

        $thumbnail = $attachment->getThumbnail();
        self::assertInstanceOf(VideoThumbnail::class, $thumbnail);
        self::assertSame('https://example.test/thumb.jpg', $thumbnail->getUrl());
        self::assertSame($thumbnail, $attachment->getThumbnail());
    }

    public function testThumbnailAsString(): void
    {
        $attachment = VideoAttachment::newFromData([
            'type' => 'video',
            'payload' => ['url' => 'https://example.test/video.mp4', 'id' => 42, 'token' => 'video-token'],
            'thumbnail' => 'https://example.test/thumb.jpg',
        ]);

        $thumbnail = $attachment->getThumbnail();
        self::assertInstanceOf(VideoThumbnail::class, $thumbnail);
        self::assertSame('https://example.test/thumb.jpg', $thumbnail->getUrl());
    }

    public function testWithoutOptionalFields(): void
    {
        $attachment = VideoAttachment::newFromData([
            'type' => 'video',
            'payload' => ['url' => 'https://example.test/video.mp4', 'id' => 42, 'token' => 'video-token'],
        ]);

        self::assertNull($attachment->getThumbnail());
        self::assertNull($attachment->getThumbnail());
        self::assertNull($attachment->getWidth());
        self::assertNull($attachment->getHeight());
        self::assertNull($attachment->getDuration());
    }
}
