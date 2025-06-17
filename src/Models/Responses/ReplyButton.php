<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use MaxMessenger\Bot\Models\Enums\ReplyButtonType;

/**
 * Reply keyboard button.
 *
 * After pressing this type of button client will send a message on behalf of user with given payload.
 *
 * @api
 */
class ReplyButton extends BaseResponseModel
{
    /**
     * @var array{
     *     type: string,
     *     text: non-empty-string,
     *     payload?: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Button payload (maxLength: 1024).
     * @api
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return non-empty-string Visible text of button (minLength: 1, maxLength: 128).
     * @api
     */
    public function getText(): string
    {
        return $this->data['text'];
    }

    /**
     * @return ReplyButtonType|null Type of reply button.
     * @api
     */
    public function getType(): ?ReplyButtonType
    {
        return ReplyButtonType::tryFrom($this->data['type']);
    }

    /**
     * @return string Type of reply button.
     * @api
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
