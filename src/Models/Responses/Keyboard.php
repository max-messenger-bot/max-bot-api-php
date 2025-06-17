<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Keyboard is two-dimension array of buttons.
 *
 * @api
 */
class Keyboard extends BaseResponseModel
{
    /**
     * @var array{
     *     buttons: list<list<array>>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    /**
     * @var list<list<Button>>|false
     */
    private array|false $buttons = false;

    /**
     * @return list<list<Button>> Two-dimensional array of buttons.
     * @api
     */
    public function getButtons(): array
    {
        return $this->buttons === false
            ? ($this->buttons = Button::newList2DFromData($this->data['buttons']))
            : $this->buttons;
    }
}
