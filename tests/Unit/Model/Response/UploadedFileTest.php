<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\UploadedFile;

final class UploadedFileTest extends Unit
{
    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidData(): iterable
    {
        yield 'нет данных' => [[]];
        yield 'нет fileId' => [['token' => 'file-token']];
        yield 'нет token' => [['fileId' => 123]];
        yield 'fileId строкой' => [['fileId' => '123', 'token' => 'file-token']];
        yield 'token числом' => [['fileId' => 123, 'token' => 456]];
    }

    public function testGetters(): void
    {
        $file = UploadedFile::newFromData(['fileId' => 123, 'token' => 'file-token']);

        self::assertSame(123, $file->getFileId());
        self::assertSame('file-token', $file->getToken());
        self::assertTrue($file->isValid());
    }

    /**
     * @param array<string, mixed> $data
     * @dataProvider invalidData
     */
    public function testIsValidWithInvalidData(array $data): void
    {
        self::assertFalse(UploadedFile::newFromData($data)->isValid());
    }
}
