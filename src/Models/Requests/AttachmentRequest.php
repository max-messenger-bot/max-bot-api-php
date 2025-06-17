<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Request to attach some data to message.
 *
 * @api
 */
abstract class AttachmentRequest extends BaseRequestModel
{
    /**
     * @var array{
     *     type: AttachmentRequestType
     * } $data
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    public function __construct(AttachmentRequestType $type)
    {
        $this->data['type'] = $type;
    }

    /**
     * @api
     */
    public function getType(): AttachmentRequestType
    {
        return $this->data['type'];
    }
}
