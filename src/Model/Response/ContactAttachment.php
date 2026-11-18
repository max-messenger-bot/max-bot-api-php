<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Вложение с контактом в сообщении.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class ContactAttachment extends Attachment
{
    /**
     * @var array{
     *     payload: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private ContactAttachmentPayload|false $payload = false;

    /**
     * @return ContactAttachmentPayload Данные прикреплённого контакта.
     */
    public function getPayload(): ContactAttachmentPayload
    {
        return $this->payload === false
            ? $this->payload = ContactAttachmentPayload::newFromData($this->data['payload'])
            : $this->payload;
    }
}
