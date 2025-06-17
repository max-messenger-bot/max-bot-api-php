<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Запрос на прикрепление клавиатуры к сообщению.
 *
 * Вы можете подключить к чат-боту в MAX inline-клавиатуру. Она позволяет разместить под сообщением бота до 210 кнопок,
 * сгруппированных в 30 рядов — до 7 кнопок в каждом (до 3, если это кнопки типа `link`, `open_app`,
 * `request_geo_location` или `request_contact`).
 */
final class InlineKeyboardAttachmentRequestPayload extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     buttons: non-empty-list<non-empty-list<Button>>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-array<non-empty-array<Button>>|null $buttons Двумерный массив кнопок (minItems: 1).
     */
    public function __construct(?array $buttons = null)
    {
        $this->required = ['buttons'];

        if ($buttons !== null) {
            $this->setButtons($buttons);
        }
    }

    /**
     * @return list<list<Button>>
     */
    public function getButtons(): array
    {
        return $this->data['buttons'];
    }

    public function issetButtons(): bool
    {
        return array_key_exists('buttons', $this->data);
    }

    /**
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок (minItems: 1).
     */
    public static function make(array $buttons): self
    {
        return new self($buttons);
    }

    /**
     * @param non-empty-array<non-empty-array<Button>>|null $buttons Двумерный массив кнопок (minItems: 1).
     */
    public static function new(?array $buttons = null): self
    {
        return new self($buttons);
    }

    /**
     * @param non-empty-array<non-empty-array<Button>> $buttons Двумерный массив кнопок (minItems: 1).
     * @return $this
     */
    public function setButtons(array $buttons): self
    {
        self::validateArray('buttons', $buttons, minItems: 1);
        self::validateArray2D('buttons', $buttons, minItems: 1);

        foreach ($buttons as &$button) {
            $button = array_values($button);
        }
        /** @var non-empty-array<non-empty-list<Button>> $buttons */

        $this->data['buttons'] = array_values($buttons);

        return $this;
    }
}
