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
     * @return SensitiveParameterValue<non-empty-string>|null API Max access token.
     */
    public function getAccessToken(): ?SensitiveParameterValue;

    /**
     * @return non-empty-string Base URL of API Max (example: `https://platform-api.max.ru`).
     */
    public function getBaseUrl(): string;

    /**
     * @return HttpClientInterface HTTP client for API requests.
     */
    public function getHttpClient(): HttpClientInterface;
}
