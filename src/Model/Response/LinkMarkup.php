<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Представляет ссылку в тексте.
 *
 * В тексте комментариев гиперссылки не поддерживаются.
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
     * @return non-empty-string URL ссылки (maxLength: 2048).
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
