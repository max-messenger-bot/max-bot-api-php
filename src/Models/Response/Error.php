<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use function is_string;

/**
 * Server returns this if there was an exception to your request.
 *
 * @api
 */
readonly class Error extends BaseResponseModel
{
    /**
     * @var array{
     *     code: string,
     *     message: string,
     *     error?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string Error code.
     * @api
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }

    /**
     * @return string|null Error.
     * @api
     */
    public function getError(): ?string
    {
        return $this->data['error'] ?? null;
    }

    /**
     * @return string Human-readable description.
     * @api
     */
    public function getMessage(): string
    {
        return $this->data['message'];
    }

    /**
     * Checks that the data passed to the model is correct for the given model.
     *
     * @api
     */
    public function isValid(): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return isset($this->data['code'], $this->data['message'])
            && is_string($this->data['code'])
            && is_string($this->data['message']);
    }
}
