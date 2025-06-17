<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Link markup.
 *
 * Represents link in text.
 *
 * @api
 */
readonly class LinkMarkup extends MarkupElement
{
    /**
     * @var array{
     *     url: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return non-empty-string Link's URL (minLength: 1, maxLength: 2048).
     * @api
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
