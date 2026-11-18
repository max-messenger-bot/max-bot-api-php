<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Клавиатура — это двумерный массив кнопок.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
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
     * @return non-empty-list<non-empty-list<Button>> Двумерный массив кнопок.
     */
    public function getButtons(): array
    {
        return $this->buttons === false
            ? ($this->buttons = Button::newList2DFromData($this->data['buttons']))
            : $this->buttons;
    }
}
