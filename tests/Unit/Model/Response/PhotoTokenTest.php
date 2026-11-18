<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\PhotoToken;

final class PhotoTokenTest extends Unit
{
    public function testGetToken(): void
    {
        self::assertSame('photo-token', PhotoToken::newFromData(['token' => 'photo-token'])->getToken());
    }
}
