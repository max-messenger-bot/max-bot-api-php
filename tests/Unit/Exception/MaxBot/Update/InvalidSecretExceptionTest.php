<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\MaxBot\Update;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Update\InvalidSecretException;
use MaxMessenger\Bot\Exception\MaxBot\Update\UpdateRequestException;

final class InvalidSecretExceptionTest extends Unit
{
    public function testExtendsUpdateRequestException(): void
    {
        self::assertInstanceOf(UpdateRequestException::class, new InvalidSecretException());
    }

    public function testMessageWithHttpCode403(): void
    {
        $exception = new InvalidSecretException();

        self::assertSame('Invalid Secret.', $exception->getMessage());
        self::assertSame(403, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
