<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use MaxMessenger\Bot\Contract\Http\StreamInterface;
use RuntimeException;

use function strlen;
use function substr;

use const SEEK_CUR;
use const SEEK_END;

/**
 * Поток {@see StreamInterface} над строкой в памяти.
 */
final class FakeBodyStream implements StreamInterface
{
    /**
     * @var non-negative-int Текущая позиция в потоке.
     */
    private int $position = 0;

    /**
     * @param non-empty-string $content Содержимое потока.
     * @param bool $seekable Можно ли перематывать поток.
     */
    public function __construct(
        private readonly string $content,
        private readonly bool $seekable = true,
    ) {}

    public function eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    public function getContents(): string
    {
        $contents = substr($this->content, $this->position);
        $this->position = strlen($this->content);

        return $contents;
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function read(int $length): string
    {
        $chunk = substr($this->content, $this->position, $length);
        $this->position += strlen($chunk);

        return $chunk;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $position = match ($whence) {
            SEEK_CUR => $this->position + $offset,
            SEEK_END => strlen($this->content) + $offset,
            default => $offset,
        };

        if ($position < 0) {
            throw new RuntimeException('The stream position cannot be negative.');
        }

        if (!$this->seekable && $position !== $this->position) {
            throw new RuntimeException('The stream is not seekable.');
        }

        $this->position = $position;
    }

    public function tell(): int
    {
        return $this->position;
    }
}
