<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;
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
    /**
     * @api
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get a property from raw data.
     *
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @var array-key|null $offset */
        if (!array_key_exists($offset ??= '', $this->data)) {
            return null;
        }

        /** @psalm-suppress UnsupportedPropertyReferenceUsage */
        $_value = &$this->data[$offset];

        if (is_array($_value)) {
            if ($_value) {
                $this->convertToRawModelArr($_value);
            }
        } elseif ($_value instanceof BaseRequestModel && !($_value instanceof self)) {
            $_value = $_value->getRawModel();
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

    private function convertToRawModelArr(array &$arr): void
    {
        foreach ($arr as &$_value) {
            if (is_array($_value)) {
                $this->convertToRawModelArr($_value);
            } elseif ($_value instanceof BaseRequestModel && !($_value instanceof self)) {
                $_value = $_value->getRawModel();
            }
        }
    }
}
