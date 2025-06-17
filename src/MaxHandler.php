<?php

declare(strict_types=1);

namespace MaxMessenger\Api;

use MaxMessenger\Api\Contracts\MaxBotConfigInterface;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class MaxHandler
{
    public readonly MaxBot $bot;

    public function __construct(string|MaxBot|MaxBotConfigInterface|null $accessTokenOrConfig = null)
    {
        $this->bot = $accessTokenOrConfig instanceof MaxBot
            ? $accessTokenOrConfig
            : new MaxBot($accessTokenOrConfig);
    }

    public function handle(Update $update): void
    {
        //$this->bot->bots();//TODO: Temp
    }

    public function handleFromGlobal(): void
    {
    }
}
