<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\ButtonType;

/**
 * Базовый класс кнопки.
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
     * @return non-empty-string Видимый текст кнопки. Чтобы он отображался полностью, рекомендуем не превышать
     *     заданное количество символов в зависимости от размещения текста: `20` символов — при 1 кнопке в ряду,
     *     `10` — при 2, `5` — при 3, `3` — при 4 (minLength: 1, maxLength: 128).
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
     * @return string Тип кнопки.
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * Creates an object using a map.
     */
    public static function newFromData(array $data): static
    {
        if (static::class !== self::class) {
            /** @psalm-var static Psalm bug */
            return parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'callback' => CallbackButton::class,
            'chat' => ChatButton::class,
            'clipboard' => ClipboardButton::class,
            'link' => LinkButton::class,
            'message' => MessageButton::class,
            'request_contact' => RequestContactButton::class,
            'request_geo_location' => RequestGeoLocationButton::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? null;

        /** @psalm-var static Psalm bug */
        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
