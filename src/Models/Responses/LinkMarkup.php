<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Представляет ссылку в тексте.
 */
class LinkMarkup extends MarkupElement
{
    /**
     * @var array{
     *     url: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string URL ссылки (minLength: 1, maxLength: 2048).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
