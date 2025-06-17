<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contracts;

use ArrayAccess;
use IteratorAggregate;
use JsonSerializable;

/**
 * Interface for models with Raw data.
 *
 * @template-extends ArrayAccess<array-key, mixed>
 * @template-extends IteratorAggregate<array-key, mixed>
 */
interface ModelInterface extends JsonSerializable, ArrayAccess, IteratorAggregate
{
    /**
     * @return array Get raw model data.
     */
    public function getRawData(): array;
}
