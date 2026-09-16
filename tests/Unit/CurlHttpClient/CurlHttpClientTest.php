<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\CurlHttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\HttpClient\Body\StringBody;
use MaxMessenger\Bot\HttpClient\Exception\HttpClientException;
use MaxMessenger\Bot\HttpClient\Exception\NetworkException;
use MaxMessenger\Bot\HttpClient\HttpRequest;

use function dirname;
use function json_decode;
use function str_contains;

final class CurlHttpClientTest extends Unit
{
    private const HTTPBIN_URL = 'https://httpbin.org';

    public function testResetOptions(): void
    {
        $client = (new CurlHttpClient())->setTimeout(1000)->setUserAgent('bot');

        $client->resetOptions();

        self::assertSame([], $client->getOptions());
    }

    public function testSetCaCertificateOptions(): void
    {
        $client = (new CurlHttpClient())
            ->setCaCertificatePath('/tmp/ca.pem')
            ->setCaCertificateDir('/tmp/certs');

        $options = $client->getOptions();
        self::assertSame('/tmp/ca.pem', $options[CURLOPT_CAINFO] ?? null);
        self::assertSame('/tmp/certs', $options[CURLOPT_CAPATH] ?? null);
    }

    public function testSetOptionWithNullRemovesOption(): void
    {
        $client = (new CurlHttpClient())->setTimeout(1000);
        self::assertArrayHasKey(CURLOPT_TIMEOUT_MS, $client->getOptions());

        $client->setTimeout(null);

        self::assertArrayNotHasKey(CURLOPT_TIMEOUT_MS, $client->getOptions());
    }

    public function testSetProxy(): void
    {
        $client = (new CurlHttpClient())->setProxy('127.0.0.1:1080', true);

        $options = $client->getOptions();
        self::assertSame('127.0.0.1:1080', $options[CURLOPT_PROXY] ?? null);
        self::assertTrue($options[CURLOPT_HTTPPROXYTUNNEL] ?? null);
        self::assertSame(CURLPROXY_SOCKS5, $options[CURLOPT_PROXYTYPE] ?? null);
    }

    public function testSetProxyWithEmptyStringRemovesProxy(): void
    {
        $client = (new CurlHttpClient())->setProxy('127.0.0.1:1080');

        $client->setProxy();

        $options = $client->getOptions();
        self::assertArrayNotHasKey(CURLOPT_PROXY, $options);
        self::assertArrayNotHasKey(CURLOPT_HTTPPROXYTUNNEL, $options);
        self::assertArrayNotHasKey(CURLOPT_PROXYTYPE, $options);
    }

    public function testSetTimeoutsAndUserAgent(): void
    {
        $client = (new CurlHttpClient())
            ->setConnectTimeout(2000)
            ->setTimeout(5000)
            ->setUserAgent('max-bot-test');

        $options = $client->getOptions();
        self::assertSame(2000, $options[CURLOPT_CONNECTTIMEOUT_MS] ?? null);
        self::assertSame(5000, $options[CURLOPT_TIMEOUT_MS] ?? null);
        self::assertSame('max-bot-test', $options[CURLOPT_USERAGENT] ?? null);
    }

    public function testSetVerifySslCertificate(): void
    {
        $client = (new CurlHttpClient())->setVerifySslCertificate(false);

        $options = $client->getOptions();
        self::assertFalse($options[CURLOPT_SSL_VERIFYPEER] ?? null);
        self::assertSame(0, $options[CURLOPT_SSL_VERIFYHOST] ?? null);

        $client->setVerifySslCertificate(true);

        $options = $client->getOptions();
        self::assertTrue($options[CURLOPT_SSL_VERIFYPEER] ?? null);
        self::assertSame(2, $options[CURLOPT_SSL_VERIFYHOST] ?? null);
    }

    /**
     * @group network
     */
    public function testRequestGetReturnsResponse(): void
    {
        $response = $this->createClient()->request(new HttpRequest('GET', self::HTTPBIN_URL . '/get?foo=bar'));

        if ($response->getHttpCode() !== 200) {
            self::markTestSkipped('Сервис httpbin.org недоступен: код ' . $response->getHttpCode());
        }

        self::assertStringContainsString('application/json', (string) $response->getContentType());
        self::assertNotNull($response->getFirstHeader('Content-Type'));
        self::assertNotSame([], $response->getHeaders());

        $data = json_decode($response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame(['foo' => 'bar'], $data['args'] ?? null);
    }

    /**
     * @group network
     */
    public function testRequestPostSendsBodyAndHeaders(): void
    {
        $request = new HttpRequest(
            'POST',
            self::HTTPBIN_URL . '/post',
            ['X-Test-Header' => 'test-value'],
            new StringBody('{"text":"привет"}', 'application/json; charset=utf-8'),
        );

        $response = $this->createClient()->request($request);

        if ($response->getHttpCode() !== 200) {
            self::markTestSkipped('Сервис httpbin.org недоступен: код ' . $response->getHttpCode());
        }

        $data = json_decode($response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame('{"text":"привет"}', $data['data'] ?? null);
        self::assertSame('test-value', $data['headers']['X-Test-Header'] ?? null);
        self::assertSame('application/json; charset=utf-8', $data['headers']['Content-Type'] ?? null);
    }

    /**
     * @group network
     */
    public function testRequestThrowsNetworkExceptionForUnknownHost(): void
    {
        $request = new HttpRequest('GET', 'https://no-such-host.invalid/');

        try {
            $this->createClient()->request($request);
            self::fail('Ожидалась ошибка сети');
        } catch (NetworkException $e) {
            self::assertInstanceOf(HttpClientException::class, $e);
            self::assertSame($request, $e->getRequest());
            self::assertContains($e->getCode(), [NetworkException::COULDNT_RESOLVE_HOST, NetworkException::COULDNT_CONNECT]);
        }
    }

    /**
     * @group network
     */
    public function testRequestThrowsNetworkExceptionOnTimeout(): void
    {
        $request = new HttpRequest('GET', self::HTTPBIN_URL . '/delay/5', timeout: 500);

        try {
            $this->createClient()->request($request);
            self::fail('Ожидалась ошибка по таймауту');
        } catch (NetworkException $e) {
            if (!str_contains($e->getMessage(), 'timed out') && !str_contains($e->getMessage(), 'Timeout')) {
                self::markTestSkipped('Сервис httpbin.org недоступен: ' . $e->getMessage());
            }

            self::assertSame(NetworkException::OPERATION_TIMEOUTED, $e->getCode());
        }
    }

    private function createClient(): CurlHttpClient
    {
        return (new CurlHttpClient())
            ->setConnectTimeout(5000)
            ->setTimeout(15_000)
            ->setUserAgent('max-bot-api-php-test')
            ->setCaCertificatePath(dirname(__DIR__, 3) . '/resources/certs/russian_trusted_ca_bundle.pem');
    }
}
