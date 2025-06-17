<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Data attachment.
 *
 * Attachment contains payload sent through `SendMessageButton`.
 *
 * @api
 */
class DataAttachment extends Attachment
{
    /**
     * @var array{
     *     data: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string Attachment data payload.
     * @api
     */
    public function getData(): string
    {
        return $this->data['data'];
    }
}
