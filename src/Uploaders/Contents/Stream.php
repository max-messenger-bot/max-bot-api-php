<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Uploaders\Contents;

use MaxMessenger\Bot\Contracts\Uploaders\StreamInterface;
use MaxMessenger\Bot\Exceptions\Uploaders\StreamException;
use MaxMessenger\Bot\Exceptions\Uploaders\StreamLogicException;

use function is_int;
use function is_resource;

final readonly class Stream implements StreamInterface
{
    /**
     * @var non-negative-int
     */
    private int $size;

    /**
     * @param resource $resource
     * @param non-empty-string $postName
     */
    public function __construct(
        public mixed $resource,
        public string $postName
    ) {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_resource($this->resource) || !$this->isSeekable()) {
            throw new StreamLogicException('A seekable stream is expected.');
        }

        $this->size = $this->extractResourceSize();
    }

    public static function fromFile(File $file): self
    {
        $resource = @fopen($file->getFileName(), 'rb');

        if (!$resource) {
            throw new StreamException('Unable to open file: ' . $file->getFileName());
        }

        return new self($resource, $file->getPostName());
    }

    /**
     * @inheritDoc
     */
    public function getPostName(): string
    {
        return $this->postName;
    }

    /**
     * @inheritDoc
     */
    public function getResource(): mixed
    {
        return $this->resource;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return non-negative-int
     */
    private function extractResourceSize(): int
    {
        $stat = @fstat($this->resource);
        if ($stat === false || !is_int($size = $stat['size'] ?? null)) {
            if (@fseek($this->resource, 0, SEEK_END) !== 0) {
                throw new StreamException('Failed to get stream size (fseek).');
            }
            $size = @ftell($this->resource);
            if ($size === false) {
                throw new StreamException('Failed to get stream size (ftell).');
            }
        }

        if ($size < 0) {
            throw new StreamLogicException('Failed to get stream size ($size).');
        }

        return $size;
    }

    private function isSeekable(): bool
    {
        $meta = @stream_get_meta_data($this->resource);

        return $meta['seekable'] ?? false;
    }
}
