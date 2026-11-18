<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\PhotoAttachmentPayload;
use MaxMessenger\Bot\Model\Response\VideoAttachmentDetails;
use MaxMessenger\Bot\Model\Response\VideoUrls;

final class VideoAttachmentDetailsTest extends Unit
{
    public function testGetters(): void
    {
        $details = VideoAttachmentDetails::newFromData([
            'token' => 'video-token',
            'urls' => ['mp4_720' => 'https://example.test/720.mp4', 'hls' => 'https://example.test/video.m3u8'],
            'thumbnail' => ['photo_id' => 1, 'url' => 'https://example.test/thumb.jpg', 'token' => 'thumb-token'],
            'width' => 1280,
            'height' => 720,
            'duration' => 60,
        ]);

        self::assertSame('video-token', $details->getToken());
        self::assertSame(1280, $details->getWidth());
        self::assertSame(720, $details->getHeight());
        self::assertSame(60, $details->getDuration());

        $urls = $details->getUrls();
        self::assertInstanceOf(VideoUrls::class, $urls);
        self::assertSame('https://example.test/720.mp4', $urls->getMp4R720());
        self::assertSame($urls, $details->getUrls());

        $thumbnail = $details->getThumbnail();
        self::assertInstanceOf(PhotoAttachmentPayload::class, $thumbnail);
        self::assertSame('thumb-token', $thumbnail->getToken());
        self::assertSame($thumbnail, $details->getThumbnail());
    }

    public function testWithoutOptionalFields(): void
    {
        $details = VideoAttachmentDetails::newFromData([
            'token' => 'video-token',
            'width' => 1280,
            'height' => 720,
            'duration' => 60,
        ]);

        self::assertNull($details->getUrls());
        self::assertNull($details->getUrls());
        self::assertNull($details->getThumbnail());
        self::assertNull($details->getThumbnail());
    }
}
