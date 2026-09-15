<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use LogicException;
use Psr\Http\Message\StreamInterface;

use function strlen;

/**
 * Минимальная реализация {@see StreamInterface} для герметичных тестов: отдаёт заданную строку,
 * остальные методы интерфейса не поддерживаются.
 */
final class FakeStream implements StreamInterface
{
    public function __construct(private readonly string $content = '') {}

    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void {}

    public function detach(): null
    {
        return null;
    }

    public function eof(): bool
    {
        return true;
    }

    public function getContents(): string
    {
        return $this->content;
    }

    public function getMetadata(?string $key = null): null
    {
        return null;
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function read(int $length): string
    {
        return $this->content;
    }

    public function rewind(): void {}

    public function seek(int $offset, int $whence = SEEK_SET): never
    {
        throw new LogicException('Not implemented.');
    }

    public function tell(): int
    {
        return 0;
    }

    public function write(string $string): never
    {
        throw new LogicException('Not implemented.');
    }
}
