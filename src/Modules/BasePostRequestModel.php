<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules;

use MaxMessenger\Api\Contracts\PostRequestInterface;

abstract class BasePostRequestModel implements PostRequestInterface
{
    protected array $data = [];

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
