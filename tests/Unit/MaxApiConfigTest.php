<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\MaxApiConfig;
use SensitiveParameterValue;

use function defined;

use const CURLOPT_CAINFO;
use const CURLOPT_CAPATH;
use const CURLOPT_SSL_OPTIONS;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLSSLOPT_NATIVE_CA;

final class MaxApiConfigTest extends Unit
{
    private const TEST_ACCESS_TOKEN = 'test-token-12345';
    private const TEST_BASE_URL = 'https://custom-api.example.com';
    private const TEST_CA_CERTIFICATE_DIR = '/etc/ssl/certs';
    private const TEST_CA_CERTIFICATE_PATH = '/etc/ssl/certs/mincifra-root-ca.pem';
    private const TEST_RETRY_ATTEMPTS = [333, 444, 555];
    private const TEST_TIMEOUT = 1234;

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

    public function testConstructWithRussianTrustedCaCertificates(): void
    {
        $config = new MaxApiConfig(useRussianTrustedCaCertificates: true);

        $path = $config->getCaCertificatePath();
        self::assertNotNull($path);
        self::assertStringEndsWith('/resources/certs/russian_trusted_ca_bundle.pem', $path);
        self::assertFileExists($path);
    }

    public function testConstructWithoutRussianTrustedCaCertificatesByDefault(): void
    {
        $config = new MaxApiConfig();

        self::assertNull($config->getCaCertificatePath());

        $httpClient = $config->getHttpClient();
        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        self::assertArrayNotHasKey(CURLOPT_CAINFO, $httpClient->getOptions());
    }

    public function testGetAttachmentRetryAttempts(): void
    {
        $config = new MaxApiConfig();

        self::assertSame($config->getRetryAttempts(), $config->getAttachmentRetryAttempts());

        $config->attachmentRetryAttempts = self::TEST_RETRY_ATTEMPTS;
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->getAttachmentRetryAttempts());

        $config->attachmentRetryAttempts = [];
        self::assertSame([], $config->getAttachmentRetryAttempts());
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

    public function testGetHttpClientForUpdatesReturnsNullWhenNotSet(): void
    {
        $config = new MaxApiConfig();

        self::assertNull($config->getHttpClientForUpdates());
    }

    public function testGetHttpClientForUpdatesReturnsSetClient(): void
    {
        $config = new MaxApiConfig();
        $httpClient = new CurlHttpClient();
        $config->setHttpClientForUpdates($httpClient);

        self::assertSame($httpClient, $config->getHttpClientForUpdates());
    }

    public function testGetHttpClientForUploaderReturnsSetClient(): void
    {
        $config = new MaxApiConfig();
        $httpClient = new CurlHttpClient();
        $config->setHttpClientForUploader($httpClient);

        self::assertSame($httpClient, $config->getHttpClientForUploader());
    }

    public function testGetHttpClientForUploaderUsesSystemCaCertificatesWhenNotSet(): void
    {
        $config = (new MaxApiConfig())
            ->setTimeout(self::TEST_TIMEOUT)
            ->setCaCertificateDir(self::TEST_CA_CERTIFICATE_DIR);

        $httpClient = $config->getHttpClientForUploader();

        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        self::assertNotSame($config->getHttpClient(), $httpClient);
        self::assertSame($httpClient, $config->getHttpClientForUploader());

        $options = $httpClient->getOptions();
        self::assertSame(self::TEST_TIMEOUT, $options[CURLOPT_TIMEOUT_MS] ?? null);
        self::assertTrue($options[CURLOPT_SSL_VERIFYPEER] ?? null);
        if (defined('CURLSSLOPT_NATIVE_CA')) {
            self::assertSame(CURLSSLOPT_NATIVE_CA, $options[CURLOPT_SSL_OPTIONS] ?? null);
        } else {
            self::assertArrayNotHasKey(CURLOPT_SSL_OPTIONS, $options);
        }
        self::assertArrayNotHasKey(CURLOPT_CAINFO, $options);
        self::assertArrayNotHasKey(CURLOPT_CAPATH, $options);
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
        self::assertTrue($options[CURLOPT_SSL_VERIFYPEER] ?? null);
        self::assertSame(2, $options[CURLOPT_SSL_VERIFYHOST] ?? null);
        self::assertSame($config->getCaCertificatePath(), $options[CURLOPT_CAINFO] ?? null);
        self::assertArrayNotHasKey(CURLOPT_CAPATH, $options);
    }

    public function testGetPostRetryAttempts(): void
    {
        $config = new MaxApiConfig();

        self::assertSame($config->getRetryAttempts(), $config->getPostRetryAttempts());

        $config->postRetryAttempts = self::TEST_RETRY_ATTEMPTS;
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->getPostRetryAttempts());

        $config->postRetryAttempts = [];
        self::assertSame([], $config->getPostRetryAttempts());
    }

    public function testGetVerifySslCertificate(): void
    {
        $config = new MaxApiConfig();

        self::assertTrue($config->getVerifySslCertificate());

        $config->verifySslCertificate = false;

        self::assertFalse($config->getVerifySslCertificate());
    }

    public function testMakeCurlHttpClientAppliesSettings(): void
    {
        $config = (new MaxApiConfig())
            ->setConnectTimeout(2000)
            ->setTimeout(5000)
            ->setCaCertificatePath(self::TEST_CA_CERTIFICATE_PATH);

        $options = $config->makeCurlHttpClient()->getOptions();

        self::assertSame(2000, $options[CURLOPT_CONNECTTIMEOUT_MS] ?? null);
        self::assertSame(5000, $options[CURLOPT_TIMEOUT_MS] ?? null);
        self::assertSame(self::TEST_CA_CERTIFICATE_PATH, $options[CURLOPT_CAINFO] ?? null);
        self::assertTrue($options[CURLOPT_SSL_VERIFYPEER] ?? null);
        if (defined('CURLSSLOPT_NATIVE_CA')) {
            self::assertSame(CURLSSLOPT_NATIVE_CA, $options[CURLOPT_SSL_OPTIONS] ?? null);
        } else {
            self::assertArrayNotHasKey(CURLOPT_SSL_OPTIONS, $options);
        }
        self::assertNotNull($options[CURLOPT_USERAGENT] ?? null);
    }

    public function testMakeCurlHttpClientCreatesNewInstance(): void
    {
        $config = new MaxApiConfig();

        self::assertNotSame($config->makeCurlHttpClient(), $config->makeCurlHttpClient());
        self::assertNotSame($config->getHttpClient(), $config->makeCurlHttpClient());
    }

    public function testMakeCurlHttpClientUsesSystemCaCertificates(): void
    {
        $config = (new MaxApiConfig())
            ->setConnectTimeout(2000)
            ->setCaCertificatePath(self::TEST_CA_CERTIFICATE_PATH)
            ->setCaCertificateDir(self::TEST_CA_CERTIFICATE_DIR);

        $options = $config->makeCurlHttpClient(useSystemCaCertificates: true)->getOptions();

        self::assertSame(2000, $options[CURLOPT_CONNECTTIMEOUT_MS] ?? null);
        self::assertTrue($options[CURLOPT_SSL_VERIFYPEER] ?? null);
        if (defined('CURLSSLOPT_NATIVE_CA')) {
            self::assertSame(CURLSSLOPT_NATIVE_CA, $options[CURLOPT_SSL_OPTIONS] ?? null);
        } else {
            self::assertArrayNotHasKey(CURLOPT_SSL_OPTIONS, $options);
        }
        self::assertArrayNotHasKey(CURLOPT_CAINFO, $options);
        self::assertArrayNotHasKey(CURLOPT_CAPATH, $options);
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

    public function testSetAttachmentRetryAttempts(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setAttachmentRetryAttempts(self::TEST_RETRY_ATTEMPTS);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->attachmentRetryAttempts);
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->getAttachmentRetryAttempts());

        self::assertSame($config, $config->setAttachmentRetryAttempts(null));
        self::assertNull($config->attachmentRetryAttempts);
        self::assertSame($config->getRetryAttempts(), $config->getAttachmentRetryAttempts());
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

    public function testSetPostRetryAttempts(): void
    {
        $config = new MaxApiConfig();

        $result = $config->setPostRetryAttempts(self::TEST_RETRY_ATTEMPTS);

        self::assertSame($config, $result);
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->postRetryAttempts);
        self::assertSame(self::TEST_RETRY_ATTEMPTS, $config->getPostRetryAttempts());

        self::assertSame($config, $config->setPostRetryAttempts(null));
        self::assertNull($config->postRetryAttempts);
        self::assertSame($config->getRetryAttempts(), $config->getPostRetryAttempts());
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
