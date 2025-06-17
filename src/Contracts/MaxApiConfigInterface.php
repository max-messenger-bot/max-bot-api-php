<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contracts;

use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use SensitiveParameterValue;

interface MaxApiConfigInterface
{
    public function getAccessToken(): ?SensitiveParameterValue;

    public function getBaseUrl(): string;

    public function getHttpClient(): HttpClientInterface;
}
