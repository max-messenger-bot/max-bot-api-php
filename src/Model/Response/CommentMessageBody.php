<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Информация о комментарии.
 */
class CommentMessageBody extends BaseResponseModel
{
    /**
     * @var array{
     *     mid: non-empty-string,
     *     seq: int,
     *     text: non-empty-string,
     *     markup?: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<MarkupElement>|false|null
     */
    private array|false|null $markup = false;

    /**
     * @return list<MarkupElement>|null Разметка текста комментария.
     */
    public function getMarkup(): ?array
    {
        return $this->markup === false
            ? ($this->markup = MarkupElement::newListFromNullableData($this->data['markup'] ?? null))
            : $this->markup;
    }

    /**
     * @return non-empty-string Уникальный ID комментария.
     */
    public function getMid(): string
    {
        return $this->data['mid'];
    }

    /**
     * @return int Порядковый номер расположения комментария в посте.
     */
    public function getSeq(): int
    {
        return $this->data['seq'];
    }

    /**
     * @return non-empty-string Текст комментария.
     */
    public function getText(): string
    {
        return $this->data['text'];
    }
}
