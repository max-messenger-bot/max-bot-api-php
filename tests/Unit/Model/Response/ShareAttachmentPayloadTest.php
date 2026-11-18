<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\ShareAttachmentPayload;

final class ShareAttachmentPayloadTest extends Unit
{
    public function testGetters(): void
    {
        $payload = ShareAttachmentPayload::newFromData([
            'url' => 'https://example.test/article',
            'token' => 'share-token',
        ]);

        self::assertSame('https://example.test/article', $payload->getUrl());
        self::assertSame('share-token', $payload->getToken());
    }

    public function testWithoutToken(): void
    {
        $payload = ShareAttachmentPayload::newFromData(['url' => 'https://example.test/article']);

        self::assertNull($payload->getToken());
    }
}
