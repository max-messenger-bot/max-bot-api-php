<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

use function array_key_exists;

/**
 * Запрос на прикрепление локации к сообщению.
 */
final class LocationAttachmentRequest extends AttachmentRequest
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     latitude: float,
     *     longitude: float
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param float|null $latitude Широта.
     * @param float|null $longitude Долгота.
     */
    public function __construct(?float $latitude = null, ?float $longitude = null)
    {
        $this->required = ['latitude', 'longitude'];

        parent::__construct(AttachmentRequestType::Location);

        if ($latitude !== null) {
            $this->setLatitude($latitude);
        }
        if ($longitude !== null) {
            $this->setLongitude($longitude);
        }
    }

    public function getLatitude(): float
    {
        return $this->data['latitude'];
    }

    public function getLongitude(): float
    {
        return $this->data['longitude'];
    }

    public function issetLatitude(): bool
    {
        return array_key_exists('latitude', $this->data);
    }

    public function issetLongitude(): bool
    {
        return array_key_exists('longitude', $this->data);
    }

    /**
     * @param float $latitude Широта.
     * @param float $longitude Долгота.
     */
    public static function make(float $latitude, float $longitude): self
    {
        return new self($latitude, $longitude);
    }

    /**
     * @param float|null $latitude Широта.
     * @param float|null $longitude Долгота.
     */
    public static function new(?float $latitude = null, ?float $longitude = null): self
    {
        return new self($latitude, $longitude);
    }

    /**
     * @param float $latitude Широта.
     * @return $this
     */
    public function setLatitude(float $latitude): self
    {
        $this->data['latitude'] = $latitude;

        return $this;
    }

    /**
     * @param float $longitude Долгота.
     * @return $this
     */
    public function setLongitude(float $longitude): self
    {
        $this->data['longitude'] = $longitude;

        return $this;
    }
}
