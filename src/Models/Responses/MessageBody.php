<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Схема, представляющая тело сообщения.
 */
class MessageBody extends BaseResponseModel
{
    /**
     * @var array{
     *     mid: non-empty-string,
     *     seq: int,
     *     text: string,
     *     attachments?: list<array>,
     *     markup?: list<array>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<Attachment>|false|null
     */
    private array|false|null $attachments = false;
    /**
     * @var list<MarkupElement>|false|null
     */
    private array|false|null $markup = false;

    /**
     * @return list<Attachment>|null Вложения сообщения. Могут быть одним из типов {@see  Attachment}.
     */
    public function getAttachments(): ?array
    {
        return $this->attachments === false
            ? ($this->attachments = Attachment::newListFromNullableData($this->data['attachments'] ?? null))
            : $this->attachments;
    }

    /**
     * @return list<MarkupElement>|null Разметка текста сообщения.
     */
    public function getMarkup(): ?array
    {
        return $this->markup === false
            ? ($this->markup = MarkupElement::newListFromNullableData($this->data['markup'] ?? null))
            : $this->markup;
    }

    /**
     * @return non-empty-string Уникальный ID сообщения (minLength: 1).
     */
    public function getMid(): string
    {
        return $this->data['mid'];
    }

    /**
     * @return int ID последовательности сообщения в чате.
     */
    public function getSeq(): int
    {
        return $this->data['seq'];
    }

    /**
     * @return string Текст сообщения.
     */
    public function getText(): string
    {
        return $this->data['text'];
    }
}
