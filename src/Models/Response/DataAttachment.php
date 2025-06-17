<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Data attachment.
 *
 * Attachment contains payload sent through `SendMessageButton`.
 *
 * @api
 */
readonly class DataAttachment extends Attachment
{
    /**
     * @var array{
     *     data: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string Attachment data payload.
     * @api
     */
    public function getData(): string
    {
        return $this->data['data'];
    }
}
