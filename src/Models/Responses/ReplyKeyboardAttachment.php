<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Custom reply keyboard in message.
 */
class ReplyKeyboardAttachment extends Attachment
{
    /**
     * @var array{
     *     buttons: non-empty-list<non-empty-list<array>>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var non-empty-list<non-empty-list<ReplyButton>>|false
     */
    private array|false $buttons = false;

    /**
     * @return non-empty-list<non-empty-list<ReplyButton>> Двумерный массив кнопок (minItems: 1).
     */
    public function getButtons(): array
    {
        return $this->buttons === false
            ? ($this->buttons = ReplyButton::newList2DFromData($this->data['buttons']))
            : $this->buttons;
    }
}
