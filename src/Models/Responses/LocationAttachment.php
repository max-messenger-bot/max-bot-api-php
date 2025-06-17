<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

class LocationAttachment extends Attachment
{
    /**
     * @var array{
     *     latitude: float,
     *     longitude: float
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return float Широта.
     */
    public function getLatitude(): float
    {
        return $this->data['latitude'];
    }

    /**
     * @return float Долгота.
     */
    public function getLongitude(): float
    {
        return $this->data['longitude'];
    }
}
