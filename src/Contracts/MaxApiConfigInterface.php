<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Contracts;

use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use SensitiveParameterValue;

/**
 * An interface implementing the configuration storage.
 *
 * You can implement it yourself.
 */
interface MaxApiConfigInterface
{
    /**
     * @return SensitiveParameterValue|null Max API access token.
     */
    public function getAccessToken(): ?SensitiveParameterValue;

    /**
     * @return string Base URL of Max API (example: `https://platform-api.max.ru`).
     */
    public function getBaseUrl(): string;

    /**
     * @return HttpClientInterface HTTP client for API requests.
     */
    public function getHttpClient(): HttpClientInterface;
}
