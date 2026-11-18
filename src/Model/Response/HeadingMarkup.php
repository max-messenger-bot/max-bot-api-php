<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Элемент разметки заголовка.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/text-formatting
 */
class HeadingMarkup extends MarkupElement
{
    /**
     * @var array{
     *     level: int<1, 4>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int<1, 4> Уровень заголовка.
     */
    public function getLevel(): int
    {
        return $this->data['level'];
    }
}
