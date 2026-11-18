<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Элемент разметки ссылки в тексте.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting
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
