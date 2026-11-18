<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\VideoUrls;

final class VideoUrlsTest extends Unit
{
    public function testEmptyUrls(): void
    {
        $urls = VideoUrls::newFromData([]);

        self::assertNull($urls->getHls());
        self::assertNull($urls->getMp4R1080());
        self::assertNull($urls->getMp4R720());
        self::assertNull($urls->getMp4R480());
        self::assertNull($urls->getMp4R360());
        self::assertNull($urls->getMp4R240());
        self::assertNull($urls->getMp4R144());
    }

    public function testGetters(): void
    {
        $urls = VideoUrls::newFromData([
            'mp4_1080' => 'https://example.test/1080.mp4',
            'mp4_720' => 'https://example.test/720.mp4',
            'mp4_480' => 'https://example.test/480.mp4',
            'mp4_360' => 'https://example.test/360.mp4',
            'mp4_240' => 'https://example.test/240.mp4',
            'mp4_144' => 'https://example.test/144.mp4',
            'hls' => 'https://example.test/video.m3u8',
        ]);

        self::assertSame('https://example.test/1080.mp4', $urls->getMp4R1080());
        self::assertSame('https://example.test/720.mp4', $urls->getMp4R720());
        self::assertSame('https://example.test/480.mp4', $urls->getMp4R480());
        self::assertSame('https://example.test/360.mp4', $urls->getMp4R360());
        self::assertSame('https://example.test/240.mp4', $urls->getMp4R240());
        self::assertSame('https://example.test/144.mp4', $urls->getMp4R144());
        self::assertSame('https://example.test/video.m3u8', $urls->getHls());
    }
}
