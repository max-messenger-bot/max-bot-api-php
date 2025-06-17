<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Link button.
 *
 * After pressing this type of button user follows the link it contains.
 *
 * @api
 */
readonly class LinkButton extends Button
{
    /**
     * @var array{
     *     url: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string Button URL (maxLength: 2048).
     * @api
     */
    public function getUrl(): string
    {
        return $this->data['url'];
    }
}
