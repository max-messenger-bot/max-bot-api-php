<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiConfig;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use Mj4444\SimpleHttpClient\CurlHttpClient;
use SensitiveParameterValue;

final class MaxApiConfigTest extends Unit
{
    private const TEST_ACCESS_TOKEN = 'test-token-12345';
    private const TEST_BASE_URL = 'https://custom-api.example.com';

    public function testConstructWithAccessToken(): void
    {
        $config = new MaxApiConfig(self::TEST_ACCESS_TOKEN);

        $accessToken = $config->getAccessToken();
        self::assertInstanceOf(SensitiveParameterValue::class, $accessToken);
    }

    public function testConstructWithCustomBaseUrl(): void
    {
        $config = new MaxApiConfig(baseUrl: self::TEST_BASE_URL);

        self::assertSame(self::TEST_BASE_URL, $config->getBaseUrl());
    }

    public function testConstructWithDefaultBaseUrl(): void
    {
        $config = new MaxApiConfig();

        self::assertSame('https://platform-api.max.ru', $config->getBaseUrl());
    }

    public function testConstructWithoutAccessToken(): void
    {
        $config = new MaxApiConfig();

        self::assertNull($config->getAccessToken());
    }

    public function testGetBaseUrl(): void
    {
        $config = new MaxApiConfig(self::TEST_ACCESS_TOKEN, baseUrl: self::TEST_BASE_URL);

        self::assertSame(self::TEST_BASE_URL, $config->getBaseUrl());
    }

    public function testGetHttpClientReturnsDefaultWhenNotSet(): void
    {
        $config = new MaxApiConfig();

        $httpClient = $config->getHttpClient();

        self::assertInstanceOf(HttpClientInterface::class, $httpClient);
    }

    public function testGetHttpClientReturnsSetClient(): void
    {
        $config = new MaxApiConfig();
        $httpClient = new CurlHttpClient();
        $config->setHttpClient($httpClient);

        self::assertSame($httpClient, $config->getHttpClient());
    }

    public function testSetAccessToken(): void
    {
        $config = new MaxApiConfig();

        self::assertNull($config->getAccessToken());

        $result = $config->setAccessToken(self::TEST_ACCESS_TOKEN);

        self::assertSame($config, $result);
        self::assertInstanceOf(SensitiveParameterValue::class, $config->getAccessToken());
    }

    public function testSetAccessTokenToNull(): void
    {
        $config = new MaxApiConfig(self::TEST_ACCESS_TOKEN);

        self::assertInstanceOf(SensitiveParameterValue::class, $config->getAccessToken());

        $config->setAccessToken(null);

        self::assertNull($config->getAccessToken());
    }

    public function testSetBaseUrl(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setBaseUrl(self::TEST_BASE_URL);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_BASE_URL, $config->getBaseUrl());
    }

    public function testSetHttpClient(): void
    {
        $config = new MaxApiConfig();
        $httpClient = new CurlHttpClient();

        $result = $config->setHttpClient($httpClient);

        self::assertSame($config, $result);
        self::assertSame($httpClient, $config->httpClient);
    }

    public function testSetHttpClientToNull(): void
    {
        $config = new MaxApiConfig();
        $httpClient = new CurlHttpClient();
        $config->setHttpClient($httpClient);

        $config->setHttpClient(null);

        self::assertNull($config->httpClient);
    }
}
