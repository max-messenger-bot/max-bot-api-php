<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiException;
use RuntimeException;

final class MaxApiExceptionTest extends Unit
{
    public function testExtendsRuntimeException(): void
    {
        self::assertInstanceOf(RuntimeException::class, new MaxApiException());
    }
}
