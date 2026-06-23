<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use LogicException;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use MaxMessenger\Bot\MaxApiClient;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use SensitiveParameterValue;

/**
 * Конфигурация, подставляющая фейковый {@see MaxHttpClientInterface} в {@see MaxApiClient}.
 */
final class FakeMaxApiConfig implements MaxApiConfigInterface
{
    public function __construct(private readonly MaxHttpClientInterface $maxHttpClient) {}

    public function getAccessToken(): SensitiveParameterValue
    {
        return new SensitiveParameterValue('test-token');
    }

    public function getBaseUrl(): string
    {
        return 'https://example.test';
    }

    public function getHttpClient(): HttpClientInterface
    {
        throw new LogicException('Not used in tests.');
    }

    public function getMaxHttpClient(): MaxHttpClientInterface
    {
        return $this->maxHttpClient;
    }

    public function getRetryAttempts(): array
    {
        return [];
    }
}
