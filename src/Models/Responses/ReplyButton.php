<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\ReplyButtonType;

/**
 * Кнопка ответа.
 *
 * After pressing this type of button client will send a message on behalf of user with given payload.
 */
class ReplyButton extends BaseResponseModel
{
    /**
     * @var array{
     *     text: non-empty-string,
     *     payload?: non-empty-string,
     *     type: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string|null Токен кнопки (minLength: 1, maxLength: 1024).
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return non-empty-string Видимый текст кнопки (minLength: 1, maxLength: 128).
     */
    public function getText(): string
    {
        return $this->data['text'];
    }

    /**
     * @return ReplyButtonType|null Тип кнопки ответа.
     */
    public function getType(): ?ReplyButtonType
    {
        return ReplyButtonType::tryFrom($this->data['type']);
    }

    /**
     * @return non-empty-string Тип кнопки ответа.
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
            'message' => SendMessageButton::class,
            'user_contact' => SendContactButton::class,
            'user_geo_location' => SendGeoLocationButton::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? null;

        /** @psalm-var static Psalm bug */
        return $className !== null
            ? $className::newFromData($data)
            : parent::newFromData($data);
    }
}
