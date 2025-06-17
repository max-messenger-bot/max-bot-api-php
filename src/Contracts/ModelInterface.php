<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contracts;

use ArrayAccess;
use IteratorAggregate;
use JsonSerializable;

interface ModelInterface extends JsonSerializable, ArrayAccess, IteratorAggregate
{
    public function getRawData(): array;
}
