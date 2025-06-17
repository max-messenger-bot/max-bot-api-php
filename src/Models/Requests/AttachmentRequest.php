<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Запрос на прикрепление данных к сообщению.
 *
 * @api
 */
abstract class AttachmentRequest extends BaseRequestModel
{
    /**
     * @var array{
     *     type: AttachmentRequestType
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param AttachmentRequestType $type Тип прикрепляемых данных.
     * @api
     */
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
