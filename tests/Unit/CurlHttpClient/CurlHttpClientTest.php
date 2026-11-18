<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\CurlHttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Contract\Http\StreamInterface;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\CurlHttpClient\Exception\CurlException;
use MaxMessenger\Bot\HttpClient\Exception\HttpClientException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\Exception\TimeoutException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\TransportErrorType;
use MaxMessenger\Bot\Tests\Support\Fake\FakeBodyStream;
use MaxMessenger\Bot\Tests\Support\Fake\FakeStreamBody;
use RuntimeException;

use function defined;
use function dirname;
use function json_decode;

use const CURLOPT_SSL_OPTIONS;
use const CURLSSLOPT_NATIVE_CA;
use const CURLSSLOPT_NO_REVOKE;

final class CurlHttpClientTest extends Unit
{
    private const HTTPBIN_URL = 'https://httpbin.org';

    /**
     * @return iterable<string, array{int, bool}> Размер тела и необходимость преобразования в строку.
     */
    public static function streamSizes(): iterable
    {
        yield 'меньше порога' => [3, true];
        yield 'равен порогу' => [4, true];
        yield 'больше порога' => [5, false];
    }

    public function testClassifierCanBeRemoved(): void
    {
        $client = new CurlHttpClient([], static function (CurlException $e): TransportErrorType {
            self::assertSame(CURLE_UNSUPPORTED_PROTOCOL, $e->getCode());

            return TransportErrorType::Timeout;
        });
        self::assertSame($client, $client->setErrorClassifier(null));

        $this->expectException(CurlException::class);
        $client->request(new HttpRequest('GET', 'unknown-protocol://example.test/'));
    }

    public function testClassifierCanDelegateToBuiltInRule(): void
    {
        $client = new CurlHttpClient([], static function (CurlException $e): ?TransportErrorType {
            self::assertSame(CURLE_UNSUPPORTED_PROTOCOL, $e->getCode());

            return null;
        });

        $this->expectException(CurlException::class);
        $client->request(new HttpRequest('GET', 'unknown-protocol://example.test/'));
    }

    public function testClassifierCanKeepCurlErrorPermanent(): void
    {
        $called = false;
        $client = new CurlHttpClient([], static function (CurlException $e) use (&$called): TransportErrorType {
            $called = true;
            self::assertSame(CURLE_UNSUPPORTED_PROTOCOL, $e->getCode());

            return TransportErrorType::Permanent;
        });

        $this->expectException(CurlException::class);
        try {
            $client->request(new HttpRequest('GET', 'unknown-protocol://example.test/'));
        } finally {
            self::assertTrue($called);
        }
    }

    public function testClassifierCanMarkCurlErrorAsTemporary(): void
    {
        $request = new HttpRequest('GET', 'unknown-protocol://example.test/');
        $client = (new CurlHttpClient())->setErrorClassifier(static function (CurlException $e): TransportErrorType {
            self::assertSame(CURLE_UNSUPPORTED_PROTOCOL, $e->getCode());

            return TransportErrorType::TemporaryNetwork;
        });

        try {
            $client->request($request);
            self::fail('Ожидалась временная ошибка');
        } catch (TemporaryNetworkException $e) {
            self::assertInstanceOf(CurlException::class, $e->getPrevious());
            self::assertSame($request, $e->getRequest());
        }
    }

    public function testClassifierCanMarkCurlErrorAsTimeout(): void
    {
        $request = new HttpRequest('GET', 'unknown-protocol://example.test/');
        $seen = null;
        $client = new CurlHttpClient([], static function (CurlException $e) use (&$seen): TransportErrorType {
            $seen = $e;

            return TransportErrorType::Timeout;
        });

        try {
            $client->request($request);
            self::fail('Ожидался таймаут');
        } catch (TimeoutException $e) {
            self::assertSame($seen, $e->getPrevious());
            self::assertSame($request, $e->getRequest());
        }
    }

    public function testRequestAcceptsUnseekableStreamAtStart(): void
    {
        $stream = new FakeBodyStream('data', seekable: false);

        $this->expectException(CurlException::class);
        (new CurlHttpClient())->request(new HttpRequest(
            'POST',
            '://invalid',
            body: new FakeStreamBody($stream, 'application/octet-stream'),
        ));
    }

    /**
     * @dataProvider streamSizes
     */
    public function testRequestConvertsSmallStreamToString(int $size, bool $convertToString): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $rewound = false;
        $stream
            ->expects(self::once())->method('seek')->with(0)
            ->willReturnCallback(static function () use (&$rewound): void {
                $rewound = true;
            });
        $stream->method('getSize')->willReturn($size);
        $stream
            ->expects($convertToString ? self::once() : self::never())
            ->method('getContents')
            ->willReturnCallback(static function () use (&$rewound): string {
                self::assertTrue($rewound);

                return 'data';
            });

        $client = new CurlHttpClient();
        self::assertSame(256 * 1024, $client->postDataStringThreshold);
        $client->postDataStringThreshold = 4;

        $this->expectException(CurlException::class);
        $client->request(new HttpRequest(
            'POST',
            '://invalid',
            body: new FakeStreamBody($stream, 'application/octet-stream'),
        ));
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

    public function testRequestRejectsUnseekableStreamAfterRead(): void
    {
        $stream = new FakeBodyStream('data', seekable: false);
        self::assertSame('d', $stream->read(1));

        $this->expectException(RuntimeException::class);
        (new CurlHttpClient())->request(new HttpRequest(
            'POST',
            '://invalid',
            body: new FakeStreamBody($stream, 'application/octet-stream'),
        ));
    }

    public function testRequestThrowsCurlExceptionForUnsupportedProtocol(): void
    {
        $request = new HttpRequest('GET', 'unknown-protocol://example.test/');

        try {
            (new CurlHttpClient())->request($request);
            self::fail('Ожидалась ошибка curl');
        } catch (CurlException $e) {
            self::assertSame(CURLE_UNSUPPORTED_PROTOCOL, $e->getCode());
            self::assertSame($request, $e->getRequest());
        }
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
        } catch (TemporaryNetworkException $e) {
            self::assertInstanceOf(HttpClientException::class, $e);
            self::assertSame($request, $e->getRequest());
            // Имя не разрешилось (CURLE_COULDNT_RESOLVE_HOST) либо подключиться не удалось
            // (CURLE_COULDNT_CONNECT): код и текст приходят из вложенной ошибки curl.
            self::assertContains($e->getCode(), [CURLE_COULDNT_RESOLVE_HOST, CURLE_COULDNT_CONNECT]);
            self::assertInstanceOf(CurlException::class, $e->getPrevious());
            self::assertSame($e->getCode(), $e->getPrevious()->getCode());
        }
    }

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

    public function testSetUseNativeCaCertificatesPreservesOtherSslOptions(): void
    {
        if (!defined('CURLSSLOPT_NATIVE_CA') || !defined('CURLSSLOPT_NO_REVOKE')) {
            self::markTestSkipped('libcurl does not support the required SSL options.');
        }

        $client = (new CurlHttpClient())->setOption(CURLOPT_SSL_OPTIONS, CURLSSLOPT_NO_REVOKE);

        $client->setUseNativeCaCertificates(true);
        $enabledOptions = $client->getOptions();
        self::assertSame(CURLSSLOPT_NO_REVOKE | CURLSSLOPT_NATIVE_CA, $enabledOptions[CURLOPT_SSL_OPTIONS]);

        $client->setUseNativeCaCertificates(false);
        $disabledOptions = $client->getOptions();
        self::assertSame(CURLSSLOPT_NO_REVOKE, $disabledOptions[CURLOPT_SSL_OPTIONS]);

        $client->setOption(CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA)->setUseNativeCaCertificates(false);
        self::assertArrayNotHasKey(CURLOPT_SSL_OPTIONS, $client->getOptions());
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

    private function createClient(): CurlHttpClient
    {
        return (new CurlHttpClient())
            ->setConnectTimeout(5000)
            ->setTimeout(15000)
            ->setUserAgent('max-bot-api-php-test')
            ->setCaCertificatePath(dirname(__DIR__, 3) . '/resources/certs/russian_trusted_ca_bundle.pem');
    }
}
