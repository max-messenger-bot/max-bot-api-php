<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Model\Enum\ButtonType;

use function array_key_exists;

/**
 * Базовый класс кнопки встроенной клавиатуры.
 *
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody/
 * @link https://dev.max.ru/docs-api/methods/POST/messages/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
abstract class Button extends BaseRequestModel
{
    use ValidateRequiredTrait;
    use ValidateTrait;

    /**
     * @var array{
     *     type: ButtonType,
     *     text: non-empty-string
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param ButtonType $type Тип кнопки.
     * @param non-empty-string|null $text Видимый текст кнопки (maxLength: 128). Чтобы он отображался
     *     полностью, рекомендуем не превышать заданное количество символов в зависимости от размещения текста:
     *     `20` символов — при одной кнопке в ряду, `10` — при двух, `5` — при трёх, `3` — при четырёх.
     */
    public function __construct(ButtonType $type, ?string $text = null)
    {
        $this->required = ['text'];

        $this->data['type'] = $type;
        if ($text !== null) {
            $this->setText($text);
        }
    }

    /**
     * @return non-empty-string
     */
    public function getText(): string
    {
        return $this->data['text'];
    }

    public function getType(): ButtonType
    {
        return $this->data['type'];
    }

    public function issetText(): bool
    {
        return array_key_exists('text', $this->data);
    }

    /**
     * @param non-empty-string $text Видимый текст кнопки (maxLength: 128). Чтобы он отображался полностью,
     *     рекомендуем не превышать заданное количество символов в зависимости от размещения текста:
     *     `20` символов — при одной кнопке в ряду, `10` — при двух, `5` — при трёх, `3` — при четырёх.
     * @return $this
     */
    public function setText(string $text): static
    {
        self::validateString('text', $text, minLength: 1, maxLength: 128);
        $this->data['text'] = $text;

        return $this;
    }
}
