<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contract;

use ArrayAccess;
use IteratorAggregate;
use JsonSerializable;

/**
 * Интерфейс модели с доступом к исходным данным.
 *
 * @template-extends ArrayAccess<array-key, mixed>
 * @template-extends IteratorAggregate<array-key, mixed>
 */
interface ModelInterface extends JsonSerializable, ArrayAccess, IteratorAggregate
{
    /**
     * @return array Исходные данные модели.
     */
    public function getRawData(): array;
}
