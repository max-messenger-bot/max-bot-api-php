<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Reply keyboard attachment.
 *
 * Custom reply keyboard in message.
 *
 * @api
 */
readonly class ReplyKeyboardAttachment extends Attachment
{
    /**
     * @var array{
     *     buttons: list<list<array>>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return list<list<ReplyButton>> Two-dimensional array of reply buttons.
     * @api
     */
    public function getButtons(): array
    {
        return ReplyButton::newList2DFromData($this->data['buttons']);
    }
}
