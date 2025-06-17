<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Simple response to request.
 *
 * @api
 */
readonly class SimpleQueryResult extends BaseResponseModel
{
    /**
     * @var array{
     *     message?: string,
     *     success: bool
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string|null Explanatory message if the result is not successful.
     * @api
     */
    public function getMessage(): ?string
    {
        return $this->data['message'] ?? null;
    }

    /**
     * @return bool `true` if request was successful. `false` otherwise.
     * @api
     */
    public function isSuccess(): bool
    {
        return ($this->data['success'] ?? null) === true;
    }
}
