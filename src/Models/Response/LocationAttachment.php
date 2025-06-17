<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Location attachment.
 *
 * @api
 */
readonly class LocationAttachment extends Attachment
{
    /**
     * @var array{
     *     latitude: float,
     *     longitude: float
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return float Latitude coordinate.
     * @api
     */
    public function getLatitude(): float
    {
        return $this->data['latitude'];
    }

    /**
     * @return float Longitude coordinate.
     * @api
     */
    public function getLongitude(): float
    {
        return $this->data['longitude'];
    }
}
