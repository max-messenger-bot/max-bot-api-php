<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\PsrHttpClient;

use MaxMessenger\Bot\Contract\Http\StreamInterface;
use Psr\Http\Message\StreamInterface as PsrStreamInterface;
use RuntimeException;

use const SEEK_SET;

/**
 * Поток {@see StreamInterface} в виде потока PSR-7.
 *
 * @internal
 */
final readonly class PsrStream implements PsrStreamInterface
{
    /**
     * @param StreamInterface $stream Поток с содержимым тела запроса.
     */
    public function __construct(
        private StreamInterface $stream,
    ) {}

    public function __toString(): string
    {
        if ($this->stream->isSeekable()) {
            $this->stream->seek(0);
        }

        return $this->stream->getContents();
    }

    /**
     * Ничего не делает: потоком распоряжается тот, кто его создал.
     */
    public function close(): void {}

    /**
     * Нижележащий ресурс недоступен.
     */
    public function detach(): null
    {
        return null;
    }

    public function eof(): bool
    {
        return $this->stream->eof();
    }

    public function getContents(): string
    {
        return $this->stream->getContents();
    }

    /**
     * @param string|null $key Имя нужного ключа.
     * @return array{}|null Сведений о потоке нет: нижележащего ресурса у него тоже нет.
     */
    public function getMetadata(?string $key = null): ?array
    {
        return $key === null ? [] : null;
    }

    /**
     * @return positive-int Размер потока в байтах.
     */
    public function getSize(): int
    {
        return $this->stream->getSize();
    }

    public function isReadable(): true
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    public function isWritable(): false
    {
        return false;
    }

    public function read(int $length): string
    {
        return $length > 0 ? $this->stream->read($length) : '';
    }

    public function rewind(): void
    {
        $this->stream->seek(0);
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    /**
     * @return non-negative-int Текущая позиция в потоке.
     */
    public function tell(): int
    {
        return $this->stream->tell();
    }

    public function write(string $string): never
    {
        throw new RuntimeException('The request body is read-only.');
    }
}
