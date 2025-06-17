<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Клавиатура - это двумерный массив кнопок.
 */
class Keyboard extends BaseResponseModel
{
    /**
     * @var array{
     *     buttons: non-empty-list<non-empty-list<array>>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var non-empty-list<non-empty-list<Button>>|false
     */
    private array|false $buttons = false;

    /**
     * @return non-empty-list<non-empty-list<Button>> Двумерный массив кнопок (minItems: 1).
     */
    public function getButtons(): array
    {
        return $this->buttons === false
            ? ($this->buttons = Button::newList2DFromData($this->data['buttons']))
            : $this->buttons;
    }
}
