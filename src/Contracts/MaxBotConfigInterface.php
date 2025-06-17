<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Contracts;

use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;

interface MaxBotConfigInterface
{
    public function getAccessToken(): ?string;

    public function getBaseUrl(): string;

    public function getHttpClient(): HttpClientInterface;
}
