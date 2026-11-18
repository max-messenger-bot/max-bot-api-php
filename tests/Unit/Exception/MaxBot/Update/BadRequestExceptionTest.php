<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Exception\MaxBot\Update;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\Exception\MaxApiException;
use MaxMessenger\Bot\Exception\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\Exception\MaxBot\Update\UpdateRequestException;

final class BadRequestExceptionTest extends Unit
{
    public function testExtendsUpdateRequestException(): void
    {
        $exception = new BadRequestException('Bad request.');

        self::assertInstanceOf(UpdateRequestException::class, $exception);
        self::assertInstanceOf(MaxApiException::class, $exception);
    }

    public function testKeepsMessageWithHttpCode400(): void
    {
        $exception = new BadRequestException('Request body is empty.');

        self::assertSame('Request body is empty.', $exception->getMessage());
        self::assertSame(400, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testKeepsPrevious(): void
    {
        $previous = new JsonException('Syntax error', JSON_ERROR_SYNTAX);

        $exception = new BadRequestException('Invalid JSON.', $previous);

        self::assertSame($previous, $exception->getPrevious());
        self::assertSame(400, $exception->getCode());
    }
}
