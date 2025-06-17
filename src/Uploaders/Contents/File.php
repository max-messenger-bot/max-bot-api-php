<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Uploaders\Contents;

use Mj4444\SimpleHttpClient\Contracts\HttpRequest\FileInterface;

final readonly class File implements FileInterface
{
    /**
     * @param non-empty-string $fileName
     * @param non-empty-string|null $postName
     */
    public function __construct(
        public string $fileName,
        public ?string $postName = null,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @inheritdoc
     */
    public function getMime(): string
    {
        return 'application/octet-stream';
    }

    /**
     * @inheritdoc
     */
    public function getPostName(): string
    {
        return basename($this->postName ?? $this->fileName) ?: 'file.dat';
    }
}
