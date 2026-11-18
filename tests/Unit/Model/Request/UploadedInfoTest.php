<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\UploadedInfo;

use function json_encode;

final class UploadedInfoTest extends Unit
{
    public function testConstructor(): void
    {
        $info = new UploadedInfo('token-1');

        self::assertTrue($info->issetToken());
        self::assertSame('token-1', $info->getToken());
        self::assertSame('{"token":"token-1"}', json_encode($info));
    }

    public function testEmptyTokenIsRejected(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "token" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        new UploadedInfo('');
    }

    public function testMake(): void
    {
        self::assertSame('token-1', UploadedInfo::make('token-1')->getToken());
    }

    public function testNewWithoutToken(): void
    {
        $info = UploadedInfo::new();

        self::assertFalse($info->issetToken());

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "token" field cannot be null.');

        $info->jsonSerialize();
    }

    public function testSetToken(): void
    {
        $info = UploadedInfo::new('token-1');

        self::assertSame($info, $info->setToken('token-2'));
        self::assertSame('token-2', $info->getToken());
        self::assertSame('{"token":"token-2"}', json_encode($info));
    }
}
