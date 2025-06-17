<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contracts;

use ArrayAccess;
use IteratorAggregate;
use JsonSerializable;

/**
 * Interface for models with Raw data.
 */
interface ModelInterface extends JsonSerializable, ArrayAccess, IteratorAggregate
{
    /**
     * @return array Raw model data.
     */
    public function getRawData(): array;
}
