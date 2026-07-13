<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiConfig;
use Mj4444\SimpleHttpClient\Contracts\HttpClientInterface;
use Mj4444\SimpleHttpClient\CurlHttpClient;
use SensitiveParameterValue;

use const CURLOPT_CAINFO;
use const CURLOPT_CAPATH;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;

final class MaxApiConfigTest extends Unit
{
    private const TEST_ACCESS_TOKEN = 'test-token-12345';
    private const TEST_BASE_URL = 'https://custom-api.example.com';
    private const TEST_CA_CERTIFICATE_DIR = '/etc/ssl/certs';
    private const TEST_CA_CERTIFICATE_PATH = '/etc/ssl/certs/mincifra-root-ca.pem';
    private const TEST_RETRY_ATTEMPTS = [333, 444, 555];
    private const TEST_TIMEOUT = 1234;

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

        self::assertSame('https://platform-api2.max.ru', $config->getBaseUrl());
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

    public function testGetCaCertificateDir(): void
    {
        $config = new MaxApiConfig();

        $config->caCertificateDir = self::TEST_CA_CERTIFICATE_DIR;

        self::assertSame(self::TEST_CA_CERTIFICATE_DIR, $config->getCaCertificateDir());
    }

    public function testGetCaCertificatePath(): void
    {
        $config = new MaxApiConfig();

        $config->caCertificatePath = self::TEST_CA_CERTIFICATE_PATH;

        self::assertSame(self::TEST_CA_CERTIFICATE_PATH, $config->getCaCertificatePath());
    }

    public function testGetConnectTimeout(): void
    {
        $config = new MaxApiConfig();

        $config->connectTimeout = self::TEST_TIMEOUT;

        self::assertSame(self::TEST_TIMEOUT, $config->getConnectTimeout());
    }

    public function testGetHttpClientAppliesCustomCaCertificate(): void
    {
        $config = new MaxApiConfig();
        $config->setCaCertificatePath(self::TEST_CA_CERTIFICATE_PATH);

        $httpClient = $config->getHttpClient();

        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        self::assertSame(self::TEST_CA_CERTIFICATE_PATH, $httpClient->getOptions()[CURLOPT_CAINFO] ?? null);
    }

    public function testGetHttpClientAppliesCustomCaCertificateDir(): void
    {
        $config = new MaxApiConfig();
        $config->setCaCertificateDir(self::TEST_CA_CERTIFICATE_DIR);

        $httpClient = $config->getHttpClient();

        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        self::assertSame(self::TEST_CA_CERTIFICATE_DIR, $httpClient->getOptions()[CURLOPT_CAPATH] ?? null);
    }

    public function testGetHttpClientDisablesSslVerification(): void
    {
        $config = new MaxApiConfig();
        $config->setVerifySslCertificate(false);

        $httpClient = $config->getHttpClient();

        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        $options = $httpClient->getOptions();
        self::assertFalse($options[CURLOPT_SSL_VERIFYPEER] ?? null);
        self::assertSame(0, $options[CURLOPT_SSL_VERIFYHOST] ?? null);
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

    public function testGetHttpClientVerifiesSslByDefault(): void
    {
        $config = new MaxApiConfig();

        $httpClient = $config->getHttpClient();

        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        $options = $httpClient->getOptions();
        self::assertArrayNotHasKey(CURLOPT_SSL_VERIFYPEER, $options);
        self::assertArrayNotHasKey(CURLOPT_SSL_VERIFYHOST, $options);
        self::assertArrayNotHasKey(CURLOPT_CAINFO, $options);
        self::assertArrayNotHasKey(CURLOPT_CAPATH, $options);
    }

    public function testGetRetryAttempts(): void
    {
        $config = new MaxApiConfig();

        $config->retryAttempts = self::TEST_RETRY_ATTEMPTS;

        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->getRetryAttempts());
    }

    public function testGetTimeout(): void
    {
        $config = new MaxApiConfig();

        $config->timeout = self::TEST_TIMEOUT;

        self::assertSame(self::TEST_TIMEOUT, $config->getTimeout());
    }

    public function testGetVerifySslCertificate(): void
    {
        $config = new MaxApiConfig();

        self::assertTrue($config->getVerifySslCertificate());

        $config->verifySslCertificate = false;

        self::assertFalse($config->getVerifySslCertificate());
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

    public function testSetCaCertificateDir(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setCaCertificateDir(self::TEST_CA_CERTIFICATE_DIR);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_CA_CERTIFICATE_DIR, $config->caCertificateDir);
        self::assertSame(self::TEST_CA_CERTIFICATE_DIR, $config->getCaCertificateDir());
    }

    public function testSetCaCertificatePath(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setCaCertificatePath(self::TEST_CA_CERTIFICATE_PATH);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_CA_CERTIFICATE_PATH, $config->caCertificatePath);
        self::assertSame(self::TEST_CA_CERTIFICATE_PATH, $config->getCaCertificatePath());
    }

    public function testSetConnectTimeout(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setConnectTimeout(self::TEST_TIMEOUT);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_TIMEOUT, $config->connectTimeout);
        self::assertSame(self::TEST_TIMEOUT, $config->getConnectTimeout());
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

    public function testSetRetryAttempts(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setRetryAttempts(self::TEST_RETRY_ATTEMPTS);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->retryAttempts);
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->getRetryAttempts());
    }

    public function testSetTimeout(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setTimeout(self::TEST_TIMEOUT);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_TIMEOUT, $config->timeout);
        self::assertSame(self::TEST_TIMEOUT, $config->getTimeout());
    }

    public function testSetVerifySslCertificate(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setVerifySslCertificate(false);

        self::assertSame($config, $result);
        self::assertFalse($config->verifySslCertificate);
        self::assertFalse($config->getVerifySslCertificate());
    }

    public function testUseRussianTrustedCaCertificates(): void
    {
        $config = new MaxApiConfig();

        $result = $config->useRussianTrustedCaCertificates();

        self::assertSame($config, $result);
        $path = $config->getCaCertificatePath();
        self::assertNotNull($path);
        self::assertFileExists($path);

        $httpClient = $config->getHttpClient();
        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        self::assertSame($path, $httpClient->getOptions()[CURLOPT_CAINFO] ?? null);
    }
}
