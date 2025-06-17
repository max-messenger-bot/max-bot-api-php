<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Request geo location button.
 *
 * After pressing this type of button client sends new message with attachment of current user geo location.
 *
 * @api
 */
readonly class RequestGeoLocationButton extends Button
{
    /**
     * @var array{
     *     quick?: bool
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return bool If *true*, sends location without asking user's confirmation.
     * @api
     */
    public function isQuick(): bool
    {
        return $this->data['quick'] ?? false;
    }
}
