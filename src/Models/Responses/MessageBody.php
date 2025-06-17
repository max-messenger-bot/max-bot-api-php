<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Schema representing body of message.
 *
 * @api
 */
class MessageBody extends BaseResponseModel
{
    /**
     * @var array{
     *     mid: string,
     *     seq: int,
     *     text: string|null,
     *     attachments?: list<array>|null,
     *     markup?: list<array>|null
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
     * @return list<Attachment>|null Message attachments.
     *     Could be one of `Attachment` type. See description of this schema.
     * @api
     */
    public function getAttachments(): ?array
    {
        return $this->attachments === false
            ? ($this->attachments = Attachment::newListFromNullableData($this->data['attachments'] ?? null))
            : $this->attachments;
    }

    /**
     * @return list<MarkupElement>|null Message text markup. See Formatting section for more info.
     * @api
     */
    public function getMarkup(): ?array
    {
        return $this->markup === false
            ? ($this->markup = MarkupElement::newListFromNullableData($this->data['markup'] ?? null))
            : $this->markup;
    }

    /**
     * @return string Unique identifier of message.
     * @api
     */
    public function getMid(): string
    {
        return $this->data['mid'];
    }

    /**
     * @return int Sequence identifier of message in chat.
     * @api
     */
    public function getSeq(): int
    {
        return $this->data['seq'];
    }

    /**
     * @return string|null Message text.
     * @api
     */
    public function getText(): ?string
    {
        return $this->data['text'];
    }
}
