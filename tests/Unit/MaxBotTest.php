<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot;

use const CURLOPT_CAINFO;

final class MaxBotTest extends Unit
{
    public function testConstructWithAccessTokenDoesNotUseRussianTrustedCaCertificatesByDefault(): void
    {
        $bot = new MaxBot('test-token', 'test-secret');

        $httpClient = $bot->getApiClient()->getMaxHttpClient()->getHttpClient();
        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        self::assertArrayNotHasKey(CURLOPT_CAINFO, $httpClient->getOptions());
    }

    public function testConstructWithAccessTokenUsesRussianTrustedCaCertificates(): void
    {
        $bot = new MaxBot('test-token', 'test-secret', useRussianTrustedCaCertificates: true);

        $httpClient = $bot->getApiClient()->getMaxHttpClient()->getHttpClient();
        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        $caInfo = $httpClient->getOptions()[CURLOPT_CAINFO] ?? null;
        self::assertIsString($caInfo);
        self::assertStringEndsWith('/resources/certs/russian_trusted_ca_bundle.pem', $caInfo);
    }

    public function testConstructWithApiClientUsesIt(): void
    {
        $apiClient = new MaxApiClient('test-token');

        $bot = new MaxBot($apiClient, useRussianTrustedCaCertificates: true);

        self::assertSame($apiClient, $bot->getApiClient());
    }
}
