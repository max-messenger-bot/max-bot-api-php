<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Schema representing body of message.
 *
 * @api
 */
readonly class MessageBody extends BaseResponseModel
{
    /**
     * @var array{
     *     mid: string,
     *     seq: int,
     *     text: string|null,
     *     attachments: list<array>|null,
     *     markup?: list<array>|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return list<Attachment>|null Message attachments.
     *     Could be one of `Attachment` type. See description of this schema.
     * @api
     */
    public function getAttachments(): ?array
    {
        return Attachment::newListFromNullableData($this->data['attachments']);
    }

    /**
     * @return list<MarkupElement>|null Message text markup. See Formatting section for more info.
     * @api
     */
    public function getMarkup(): ?array
    {
        return MarkupElement::newListFromNullableData($this->data['markup'] ?? null);
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
