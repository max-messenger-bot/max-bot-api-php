<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Вложение содержит полезную нагрузку, отправленную через `SendMessageButton`.
 */
class DataAttachment extends Attachment
{
    /**
     * @var array{
     *     data: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Полезная нагрузка вложения (minLength: 1).
     */
    public function getData(): string
    {
        return $this->data['data'];
    }
}
