<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use MaxMessenger\Bot\Models\Enums\ButtonType;

/**
 * Keyboard button.
 *
 * @api
 */
readonly class Button extends BaseResponseModel
{
    /**
     * @var array{
     *     type: string,
     *     text: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return non-empty-string Visible text of button (minLength: 1, maxLength: 128).
     * @api
     */
    public function getText(): string
    {
        return $this->data['text'];
    }

    /**
     * @return ButtonType Type of button.
     * @api
     */
    public function getType(): ButtonType
    {
        return ButtonType::from($this->data['type']);
    }

    /**
     * @return string Type of button.
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
            parent::newFromData($data);
        }

        /** @var array<string, class-string<self>> $classList */
        $classList = [
            'callback' => CallbackButton::class,
            'chat' => ChatButton::class,
            'link' => LinkButton::class,
            'message' => MessageButton::class,
            'request_contact' => RequestContactButton::class,
            'request_geo_location' => RequestGeoLocationButton::class,
        ];
        /** @var array{type: string} $data */
        $className = $classList[$data['type']] ?? self::class;

        return $className::newFromData($data);
    }
}
