<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\CurlHttpClient;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Contract\Http\HttpResponseInterface;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\CurlHttpClient\Exception\CurlException;
use MaxMessenger\Bot\HttpClient\Body\StringBody;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\Exception\TimeoutException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\Tests\Support\Fake\FakeBodyStream;
use MaxMessenger\Bot\Tests\Support\Fake\FakeStreamBody;
use MaxMessenger\Bot\Tests\Support\Server\LocalHttpServer;

use function fclose;
use function json_decode;
use function microtime;
use function str_repeat;
use function stream_socket_get_name;
use function stream_socket_server;
use function strlen;
use function strrpos;
use function substr;

use const CURLE_COULDNT_CONNECT;
use const CURLE_OPERATION_TIMEOUTED;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_USERAGENT;

/**
 * Запросы {@see CurlHttpClient} к встроенному веб-серверу PHP на `127.0.0.1`.
 */
final class CurlHttpClientLocalServerTest extends Unit
{
    private static ?LocalHttpServer $server = null;

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function methodsWithBody(): iterable
    {
        yield 'PATCH' => ['PATCH'];
        yield 'POST' => ['POST'];
        yield 'PUT' => ['PUT'];
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$server = LocalHttpServer::start();
    }

    public static function tearDownAfterClass(): void
    {
        self::$server?->stop();
        self::$server = null;
        parent::tearDownAfterClass();
    }

    public function testConnectionRefusedIsTemporaryNetworkError(): void
    {
        $request = new HttpRequest('GET', 'http://127.0.0.1:' . self::closedPort() . '/');

        try {
            (new CurlHttpClient())->request($request);
            self::fail('Ожидалась ошибка соединения');
        } catch (TemporaryNetworkException $e) {
            self::assertNotInstanceOf(TimeoutException::class, $e);
            self::assertSame(CURLE_COULDNT_CONNECT, $e->getCode());
            self::assertSame($request, $e->getRequest());
            self::assertInstanceOf(CurlException::class, $e->getPrevious());
        }
    }

    public function testConstructorOptionsAreApplied(): void
    {
        $client = new CurlHttpClient([CURLOPT_USERAGENT => 'ctor-agent']);

        $data = self::decode($client->request(new HttpRequest('GET', self::url('/echo'))));

        self::assertSame('ctor-agent', $data['headers']['User-Agent'] ?? null);
    }

    public function testDeleteSendsNoBody(): void
    {
        $data = self::decode((new CurlHttpClient())->request(new HttpRequest('DELETE', self::url('/echo?id=1'))));

        self::assertSame('DELETE', $data['method']);
        self::assertSame('/echo?id=1', $data['uri']);
        self::assertSame('', $data['body']);
        self::assertArrayNotHasKey('Content-Type', $data['headers']);
    }

    public function testHeadReturnsHeadersWithoutBody(): void
    {
        $client = new CurlHttpClient();
        $response = $client->request(new HttpRequest('HEAD', self::url('/head'), timeout: 1000));

        self::assertSame(200, $response->getHttpCode());
        self::assertSame('5', $response->getFirstHeader('Content-Length'));
        self::assertSame('', $response->getBody());
        self::assertSame('hello', $client->request(new HttpRequest('GET', self::url('/head')))->getBody());
    }

    public function testLowSpeedLimitAbortsStalledResponse(): void
    {
        $request = new HttpRequest('GET', self::url('/delay/4000'), lowSpeedLimit: 1, lowSpeedTime: 1);
        $start = microtime(true);

        try {
            (new CurlHttpClient())->request($request);
            self::fail('Ожидалось прерывание медленного ответа');
        } catch (TimeoutException $e) {
            self::assertSame(CURLE_OPERATION_TIMEOUTED, $e->getCode());
            self::assertLessThan(3.5, microtime(true) - $start);
        }
    }

    /**
     * @param non-empty-string $method
     * @dataProvider methodsWithBody
     */
    public function testMethodWithoutBodySendsEmptyBody(string $method): void
    {
        $data = self::decode((new CurlHttpClient())->request(new HttpRequest($method, self::url('/echo'))));

        self::assertSame($method, $data['method']);
        self::assertSame('', $data['body']);
        self::assertSame('0', $data['headers']['Content-Length'] ?? null);
    }

    public function testNotPersistentHandleServesEveryRequest(): void
    {
        $client = new CurlHttpClient();
        $client->usePersistentCurlHandle = false;

        self::assertSame('GET', self::decode($client->request(new HttpRequest('GET', self::url('/echo'))))['method']);
        self::assertSame(
            'DELETE',
            self::decode($client->request(new HttpRequest('DELETE', self::url('/echo'))))['method'],
        );
    }

    public function testPersistentHandleIsResetBetweenRequests(): void
    {
        $client = new CurlHttpClient();
        $client->request(new HttpRequest(
            'POST',
            self::url('/echo'),
            ['X-First' => 'yes'],
            body: new StringBody('{"a":1}', 'application/json'),
        ));

        $data = self::decode($client->request(new HttpRequest('GET', self::url('/echo'))));

        self::assertSame('GET', $data['method']);
        self::assertSame('', $data['body']);
        self::assertArrayNotHasKey('X-First', $data['headers']);
        self::assertArrayNotHasKey('Content-Type', $data['headers']);
    }

    public function testRedirectReturnsOnlyFinalResponseHeaders(): void
    {
        $client = (new CurlHttpClient())->setOption(CURLOPT_FOLLOWLOCATION, true);
        $response = $client->request(new HttpRequest('GET', self::url('/redirect')));

        self::assertSame(200, $response->getHttpCode());
        self::assertSame('application/json', $response->getContentType());
        self::assertSame('application/json', $response->getFirstHeader('Content-Type'));
        self::assertNull($response->getFirstHeader('X-Intermediate'));
        self::assertNull($response->getFirstHeader('Location'));
        self::assertSame(['first', 'second'], $response->getHeaders()['X-Multi'] ?? null);
        self::assertSame('/headers', self::decode($response)['uri']);
    }

    public function testRequestSendsMethodUrlHeadersAndBody(): void
    {
        $response = (new CurlHttpClient())->request(new HttpRequest(
            'POST',
            self::url('/echo?chat_id=100'),
            ['Authorization' => 'token', 'X-Test' => 'значение'],
            body: new StringBody('{"text":"привет"}', 'application/json; charset=utf-8'),
        ));

        self::assertSame(200, $response->getHttpCode());
        self::assertSame('application/json', $response->getContentType());
        $data = self::decode($response);
        self::assertSame('POST', $data['method']);
        self::assertSame('/echo?chat_id=100', $data['uri']);
        self::assertSame('{"text":"привет"}', $data['body']);
        self::assertSame('token', $data['headers']['Authorization'] ?? null);
        self::assertSame('значение', $data['headers']['X-Test'] ?? null);
        self::assertSame('application/json; charset=utf-8', $data['headers']['Content-Type'] ?? null);
    }

    public function testRequestTimeoutOverridesClientTimeout(): void
    {
        $client = (new CurlHttpClient())->setTimeout(10000);
        $start = microtime(true);

        try {
            $client->request(new HttpRequest('GET', self::url('/delay/3000'), timeout: 300));
            self::fail('Ожидалась ошибка по таймауту');
        } catch (TimeoutException $e) {
            self::assertSame(CURLE_OPERATION_TIMEOUTED, $e->getCode());
            self::assertLessThan(2.5, microtime(true) - $start);
        }
    }

    public function testResponseHeadersAreCollected(): void
    {
        $response = (new CurlHttpClient())->request(new HttpRequest('GET', self::url('/headers')));

        self::assertSame(['first', 'second'], $response->getHeaders()['X-Multi'] ?? null);
        self::assertSame('first', $response->getFirstHeader('X-Multi'));
        self::assertSame('value', $response->getFirstHeader('X-Spaced'));
    }

    public function testResponseWithErrorStatusIsReturned(): void
    {
        $response = (new CurlHttpClient())->request(new HttpRequest('GET', self::url('/status/404')));

        self::assertSame(404, $response->getHttpCode());
        self::assertSame('GET', self::decode($response)['method']);
    }

    public function testResponseWithoutContentType(): void
    {
        $response = (new CurlHttpClient())->request(new HttpRequest('GET', self::url('/no-content-type')));

        self::assertSame(200, $response->getHttpCode());
        self::assertNull($response->getContentType());
        self::assertSame('plain', $response->getBody());
    }

    public function testSmallStreamBodyIsSentFromStart(): void
    {
        $stream = new FakeBodyStream('0123456789');
        $stream->read(4);

        $data = self::decode((new CurlHttpClient())->request(new HttpRequest(
            'POST',
            self::url('/echo'),
            body: new FakeStreamBody($stream, 'application/octet-stream'),
        )));

        self::assertSame('0123456789', $data['body']);
        self::assertSame('application/octet-stream', $data['headers']['Content-Type'] ?? null);
    }

    public function testStreamBodyIsUploadedFromStart(): void
    {
        $content = str_repeat('поток', 210_000);
        $stream = new FakeBodyStream($content);
        $stream->read(2);
        $client = new CurlHttpClient();
        $client->postDataStringThreshold = 1000;

        $data = self::decode($client->request(new HttpRequest(
            'PUT',
            self::url('/echo'),
            body: new FakeStreamBody($stream, 'application/octet-stream'),
        )));

        self::assertSame('PUT', $data['method']);
        self::assertSame($content, $data['body']);
        self::assertSame((string) strlen($content), $data['headers']['Content-Length'] ?? null);
        self::assertArrayNotHasKey('Expect', $data['headers']);
    }

    /**
     * @return int Порт `127.0.0.1`, на котором никто не принимает соединения.
     */
    private static function closedPort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertNotFalse($socket);
        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);
        $port = (int) substr($name, (int) strrpos($name, ':') + 1);
        self::assertGreaterThan(0, $port);

        return $port;
    }

    /**
     * @return array{method: string, uri: string, headers: array<string, string>, body: string}
     */
    private static function decode(HttpResponseInterface $response): array
    {
        /** @var array{method: string, uri: string, headers: array<string, string>, body: string} $data */
        $data = json_decode($response->getBody(), true);
        self::assertIsArray($data);

        return $data;
    }

    /**
     * @param non-empty-string $path
     * @return non-empty-string
     */
    private static function url(string $path): string
    {
        $server = self::$server;
        self::assertNotNull($server);

        return $server->baseUrl . $path;
    }
}
