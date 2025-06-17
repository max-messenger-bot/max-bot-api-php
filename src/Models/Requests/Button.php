<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\ButtonType;

use function array_key_exists;

/**
 * Базовый класс кнопки.
 */
abstract class Button extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

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
     * @param non-empty-string|null $text Видимый текст кнопки (minLength: 1, maxLength: 128). Чтобы он отображался
     *     полностью, рекомендуем не превышать заданное количество символов в зависимости от размещения текста:
     *     `20` символов — при 1 кнопке в ряду, `10` — при 2, `5` — при 3, `3` — при 4.
     */
    public function __construct(ButtonType $type, ?string $text = null)
    {
        $this->required[] = 'text';

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
     * @param non-empty-string $text Видимый текст кнопки (minLength: 1, maxLength: 128). Чтобы он отображался
     *     полностью, рекомендуем не превышать заданное количество символов в зависимости от размещения текста:
     *     `20` символов — при 1 кнопке в ряду, `10` — при 2, `5` — при 3, `3` — при 4.
     * @return $this
     */
    public function setText(string $text): static
    {
        self::validateString('text', $text, minLength: 1, maxLength: 128);
        $this->data['text'] = $text;

        return $this;
    }
}
