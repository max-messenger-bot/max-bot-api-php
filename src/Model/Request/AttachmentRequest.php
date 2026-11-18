<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\AttachmentType;

/**
 * Запрос на прикрепление данных к сообщению.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 */
abstract class AttachmentRequest extends BaseRequestModel
{
    /**
     * @var array{
     *     type: AttachmentType
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param AttachmentType $type Тип прикрепляемых данных.
     */
    public function __construct(AttachmentType $type)
    {
        $this->data['type'] = $type;
    }

    public function getType(): AttachmentType
    {
        return $this->data['type'];
    }
}
