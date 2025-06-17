<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Contracts;

interface RequestInterface
{
    /**
     * @return array<string|int>
     */
    public function getRequestQuery(): array;
}
