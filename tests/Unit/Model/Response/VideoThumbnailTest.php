<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\VideoThumbnail;

final class VideoThumbnailTest extends Unit
{
    public function testNewFromData(): void
    {
        $thumbnail = VideoThumbnail::newFromData(['url' => 'https://example.test/thumb.jpg']);

        self::assertSame('https://example.test/thumb.jpg', $thumbnail->getUrl());
    }

    public function testNewFromUrl(): void
    {
        $thumbnail = VideoThumbnail::newFromUrl('https://example.test/thumb.jpg');

        self::assertSame('https://example.test/thumb.jpg', $thumbnail->getUrl());
    }
}
