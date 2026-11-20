<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ButtonType;

/**
 * Базовый класс кнопки встроенной клавиатуры.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
class Button extends BaseResponseModel
{
    /**
     * @var array{
     *     type: non-empty-string,
     *     text: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Видимый текст кнопки (maxLength: 128). Чтобы он отображался полностью, рекомендуем
     *     не превышать заданное количество символов в зависимости от размещения текста:
     *     `20` символов — при одной кнопке в ряду, `10` — при двух, `5` — при трёх, `3` — при четырёх.
     */
    public function getText(): string
    {
        return $this->data['text'];
    }

    /**
     * @return ButtonType|null Тип кнопки.
     */
    public function getType(): ?ButtonType
    {
        return ButtonType::tryFrom($this->data['type']);
    }

    /**
     * @return non-empty-string Тип кнопки. Значения описаны в {@see ButtonType}.
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * Создаёт кнопку нужного типа по данным ответа API.
     *
     * @param array $data Данные кнопки из ответа API.
     */
    public static function newFromData(array $data): static
    {
        if (static::class !== self::class) {
            return parent::newFromData($data);
        }

        $classList = [
            'callback' => CallbackButton::class,
            'clipboard' => ClipboardButton::class,
            'link' => LinkButton::class,
            'message' => MessageButton::class,
            'open_app' => OpenAppButton::class,
            'request_contact' => RequestContactButton::class,
            'request_geo_location' => RequestGeoLocationButton::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? null;

        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
