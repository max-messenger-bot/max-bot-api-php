<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\PsrHttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\PsrHttpClient\PsrStream;
use MaxMessenger\Bot\Tests\Support\Fake\FakeBodyStream;
use RuntimeException;

use function str_repeat;
use function strlen;

use const SEEK_CUR;
use const SEEK_END;

final class PsrStreamTest extends Unit
{
    public function testCloseLeavesStreamUntouched(): void
    {
        $body = new FakeBodyStream('0123456789');
        $stream = new PsrStream($body);
        $stream->read(4);

        $stream->close();

        self::assertSame(4, $body->tell());
        self::assertSame('456789', $stream->getContents());
        self::assertSame(10, $stream->getSize());
    }

    public function testDetachAndMetadata(): void
    {
        $stream = new PsrStream(new FakeBodyStream('привет'));

        self::assertNull($stream->detach());
        self::assertSame([], $stream->getMetadata());
        self::assertNull($stream->getMetadata('size'));
    }

    public function testGetContentsReadsStreamToEnd(): void
    {
        $content = str_repeat('a', 70000);
        $stream = new PsrStream(new FakeBodyStream($content));

        self::assertSame($content, $stream->getContents());
        self::assertTrue($stream->eof());
        self::assertSame(strlen($content), $stream->tell());
    }

    public function testGetContentsReturnsEmptyStringAtEndOfStream(): void
    {
        $stream = new PsrStream(new FakeBodyStream('x'));
        $stream->read(1);

        self::assertSame('', $stream->getContents());
    }

    public function testModes(): void
    {
        $stream = new PsrStream(new FakeBodyStream('привет'));

        self::assertTrue($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertTrue($stream->isSeekable());
        self::assertFalse((new PsrStream(new FakeBodyStream('привет', seekable: false)))->isSeekable());
    }

    public function testReadReturnsEmptyStringForNonPositiveLength(): void
    {
        $stream = new PsrStream(new FakeBodyStream('привет'));

        self::assertSame('', $stream->read(0));
        self::assertSame('', $stream->read(-1));
        self::assertSame(0, $stream->tell());
    }

    public function testReadsByChunksAndRewinds(): void
    {
        $stream = new PsrStream(new FakeBodyStream('0123456789'));

        self::assertSame(10, $stream->getSize());
        self::assertSame('0123', $stream->read(4));
        self::assertSame(4, $stream->tell());
        self::assertFalse($stream->eof());

        $stream->rewind();

        self::assertSame(0, $stream->tell());
        self::assertSame('01234', $stream->read(5));

        $stream->seek(-2, SEEK_END);

        self::assertSame('89', $stream->read(5));
        self::assertTrue($stream->eof());
    }

    public function testRewindNotSeekableStreamAfterReadFails(): void
    {
        $stream = new PsrStream(new FakeBodyStream('0123456789', seekable: false));
        $stream->read(4);

        $this->expectException(RuntimeException::class);

        $stream->rewind();
    }

    public function testSeekFromCurrentPosition(): void
    {
        $stream = new PsrStream(new FakeBodyStream('0123456789'));
        $stream->read(2);

        $stream->seek(3, SEEK_CUR);

        self::assertSame(5, $stream->tell());
        self::assertSame('56', $stream->read(2));
    }

    public function testSeekPassesStreamErrorThrough(): void
    {
        $stream = new PsrStream(new FakeBodyStream('0123456789'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The stream position cannot be negative.');

        $stream->seek(-1);
    }

    public function testToStringReadsFromCurrentPositionWhenStreamIsNotSeekable(): void
    {
        $stream = new PsrStream(new FakeBodyStream('0123456789', seekable: false));
        $stream->read(4);

        self::assertSame('456789', (string) $stream);
    }

    public function testToStringRereadsStreamFromStart(): void
    {
        $stream = new PsrStream(new FakeBodyStream('привет'));
        $stream->read(2);

        self::assertSame('привет', (string) $stream);
    }

    public function testWriteIsNotAllowed(): void
    {
        $stream = new PsrStream(new FakeBodyStream('привет'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The request body is read-only.');

        $stream->write('данные');
    }
}
