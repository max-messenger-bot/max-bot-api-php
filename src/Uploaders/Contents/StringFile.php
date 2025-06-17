<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Uploaders\Contents;

use Mj4444\SimpleHttpClient\Contracts\HttpRequest\StringFileInterface;

final readonly class StringFile implements StringFileInterface
{
    /**
     * @param non-empty-string $postName
     */
    public function __construct(
        public string $data,
        public string $postName
    ) {
    }

    public function getData(): string
    {
        return $this->data;
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
        return basename($this->postName) ?: 'file.dat';
    }
}
