<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Reply keyboard attachment.
 *
 * Custom reply keyboard in message.
 *
 * @api
 */
class ReplyKeyboardAttachment extends Attachment
{
    /**
     * @var array{
     *     buttons: list<list<array>>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<list<ReplyButton>>|false
     */
    private array|false $buttons = false;

    /**
     * @return list<list<ReplyButton>> Two-dimensional array of reply buttons.
     * @api
     */
    public function getButtons(): array
    {
        return $this->buttons === false
            ? ($this->buttons = ReplyButton::newList2DFromData($this->data['buttons']))
            : $this->buttons;
    }
}
