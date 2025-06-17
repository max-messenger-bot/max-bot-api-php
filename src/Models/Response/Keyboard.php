<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Keyboard is two-dimension array of buttons.
 *
 * @api
 */
readonly class Keyboard extends BaseResponseModel
{
    /**
     * @var array{
     *     buttons: list<list<array>>
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return list<list<Button>> Two-dimensional array of buttons.
     * @api
     */
    public function getButtons(): array
    {
        return Button::newList2DFromData($this->data['buttons']);
    }
}
