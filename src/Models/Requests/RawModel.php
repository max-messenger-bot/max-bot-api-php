<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function is_array;

/**
 * Raw data query model.
 *
 * > [!CAUTION]
 * > Do not use this class if you are unfamiliar with the Max platform API data format.
 * > Do not use this class if you are not an experienced programmer.
 * > You are solely responsible for the correctness of the data passed.
 * > Incorrect data structure may result in an incorrect request format and subsequent explicit and implicit errors.
 *
 * @api
 */
class RawModel extends BaseRequestModel
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get a property from raw data.
     *
     * @psalm-suppress MixedArrayOffset, MixedArrayTypeCoercion
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public function offsetGet(mixed $offset): mixed
    {
        $_value = $this->data[$offset ?? ''] ?? null;

        if (is_array($_value)) {
            $saveRequired = false;
            foreach ($_value as &$_item) {
                if ($_item instanceof BaseRequestModel && !($_item instanceof self)) {
                    $_item = $_item->getRawModel();
                    $saveRequired = true;
                }
            }
            unset($_item);
            if ($saveRequired) {
                $this->data[$offset ?? ''] = $_value;
            }
        } elseif ($_value instanceof BaseRequestModel && !($_value instanceof self)) {
            $this->data[$offset ?? ''] = ($_value = $_value->getRawModel());
        }

        return $_value;
    }


    /**
     * Set a property in raw data.
     *
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        /** @psalm-suppress MixedArrayOffset */
        $this->data[$offset ?? ''] = $value;
    }

    /**
     * Remove property from raw data.
     *
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public function offsetUnset(mixed $offset): void
    {
        /** @psalm-suppress MixedArrayOffset, MixedArrayTypeCoercion */
        unset($this->data[$offset ?? '']);
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
