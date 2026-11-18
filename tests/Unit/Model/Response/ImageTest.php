<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\Image;

final class ImageTest extends Unit
{
    public function testGetUrl(): void
    {
        $image = Image::newFromData(['url' => 'https://example.test/icon.png']);

        self::assertSame('https://example.test/icon.png', $image->getUrl());
    }
}
