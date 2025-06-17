<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules;

use MaxMessenger\Api\MaxBotRawClient;

trait ModuleTrait
{
    public function __construct(
        protected readonly MaxBotRawClient $client
    ) {
    }
}
