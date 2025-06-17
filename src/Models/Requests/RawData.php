<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

/**
 * Raw data query model.
 *
 * > [!CAUTION]
 * > Do not use this class if you are unfamiliar with the Max platform API data format.
 * > Do not use this class if you are not an experienced programmer.
 * > You are solely responsible for the correctness of the data passed.
 * > Incorrect data structure may result in an incorrect request format and subsequent explicit and implicit errors.
 *
 * @template-extends BaseRequestModel<array>
 * @api
 */
class RawData extends BaseRequestModel
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Set a property in raw data.
     *
     * @api
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Remove property from raw data.
     *
     * @api
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }


    /**
     * @return $this
     * @api
     */
    public function setRawData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
