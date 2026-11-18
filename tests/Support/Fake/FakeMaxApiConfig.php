<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use LogicException;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use SensitiveParameterValue;

/**
 * Конфигурация для тестов без реального HTTP-клиента.
 */
final class FakeMaxApiConfig implements MaxApiConfigInterface
{
    public function getAccessToken(): SensitiveParameterValue
    {
        return new SensitiveParameterValue('test-token');
    }

    public function getAttachmentRetryAttempts(): array
    {
        return $this->getRetryAttempts();
    }

    public function getBaseUrl(): string
    {
        return 'https://example.test';
    }

    public function getHttpClient(): HttpClientInterface
    {
        throw new LogicException('Not used in tests.');
    }

    public function getHttpClientForUpdates(): null
    {
        return null;
    }

    public function getHttpClientForUploader(): HttpClientInterface
    {
        throw new LogicException('Not used in tests.');
    }

    public function getPostRetryAttempts(): array
    {
        return $this->getRetryAttempts();
    }

    public function getRetryAttempts(): array
    {
        return [];
    }
}
