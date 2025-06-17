<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

/**
 * Запрос на прикрепление данных к сообщению.
 */
abstract class AttachmentRequest extends BaseRequestModel
{
    /**
     * @var array{
     *     type: AttachmentRequestType
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param AttachmentRequestType $type Тип прикрепляемых данных.
     */
    public function __construct(AttachmentRequestType $type)
    {
        $this->data['type'] = $type;
    }

    public function getType(): AttachmentRequestType
    {
        return $this->data['type'];
    }
}
