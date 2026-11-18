<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\PsrHttpClient;

use Closure;
use Codeception\Test\Unit;
use LogicException;
use MaxMessenger\Bot\HttpClient\Body\StringBody;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\Exception\TimeoutException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\TransportErrorType;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;
use MaxMessenger\Bot\PsrHttpClient\PsrStream;
use MaxMessenger\Bot\Tests\Support\Fake\FakeBodyStream;
use MaxMessenger\Bot\Tests\Support\Fake\FakePsrClient;
use MaxMessenger\Bot\Tests\Support\Fake\FakePsrClientException;
use MaxMessenger\Bot\Tests\Support\Fake\FakePsrClientWithFactories;
use MaxMessenger\Bot\Tests\Support\Fake\FakePsrNetworkException;
use MaxMessenger\Bot\Tests\Support\Fake\FakeStreamBody;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

use function strlen;

final class PsrHttpClientTest extends Unit
{
    /**
     * @return iterable<string, array{positive-int|null, positive-int|null}> Ограничения скорости передачи.
     */
    public static function lowSpeedLimits(): iterable
    {
        yield 'только предел скорости' => [100, null];
        yield 'только время' => [null, 10];
        yield 'оба ограничения' => [100, 10];
    }

    /**
     * @return iterable<string, array{string}> Тексты сетевых ошибок клиента PSR-18.
     */
    public static function networkErrorMessages(): iterable
    {
        yield 'неподдерживаемый протокол' => ['cURL error 1: Unsupported protocol'];
        yield 'неверный URL' => ['cURL error 3: URL using bad/illegal format'];
        yield 'сертификат' => ['cURL error 60: SSL certificate problem: unable to get local issuer certificate'];
        yield 'клиент на потоках PHP' => ['HTTP request failed!'];
        yield 'Symfony' => ['Could not resolve host "example.test" for "https://example.test/me".'];
        yield 'произвольный текст' => ['Transport failed'];
    }

    /**
     * @return iterable<string, array{int}> Коды исключения PSR-клиента.
     */
    public static function networkExceptionCodes(): iterable
    {
        yield 'произвольный код' => [42];
        yield 'код постоянной ошибки curl' => [60];
    }

    /**
     * @return iterable<string, array{string}> Тексты постоянных ошибок curl.
     */
    public static function permanentCurlMessages(): iterable
    {
        yield 'неподдерживаемый протокол' => ['cURL error 1: Unsupported protocol'];
        yield 'неверный URL' => ['cURL error 3: URL using bad/illegal format'];
        yield 'сертификат' => ['cURL error 60: SSL certificate problem: unable to get local issuer certificate'];
    }

    /**
     * @return iterable<string, array{string}> Тексты временных ошибок curl.
     */
    public static function temporaryCurlMessages(): iterable
    {
        yield 'прокси не найден' => ['cURL error 5: Could not resolve proxy: proxy.test'];
        yield 'хост не найден' => ['cURL error 6: Could not resolve host: example.test'];
        yield 'отказ соединения' => ['cURL error 7: Failed to connect to example.test port 443: Connection refused'];
        yield 'неполный ответ' => ['cURL error 18: transfer closed with 10 bytes remaining to read'];
        yield 'TLS-рукопожатие' => ['cURL error 35: OpenSSL SSL_connect: Connection reset by peer'];
        yield 'пустой ответ' => ['cURL error 52: Empty reply from server'];
        yield 'ошибка отправки' => ['cURL error 55: Connection died, tried 5 times before giving up'];
        yield 'ошибка приёма' => ['cURL error 56: Recv failure: Connection reset by peer'];
        yield 'код в середине текста' => ['Request failed (cURL error 56: Recv failure: Connection reset by peer)'];
    }

    public function testClassifierCanDelegateToBuiltInRule(): void
    {
        $previous = new FakePsrNetworkException(
            'cURL error 6: Could not resolve host',
            (new Psr17Factory())->createRequest('GET', 'https://example.test/me'),
        );
        $client = $this->createClient(
            new FakePsrClient(exception: $previous),
            static function (ClientExceptionInterface $e) use ($previous): ?TransportErrorType {
                self::assertSame($previous, $e);

                return null;
            },
        );

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидалась временная ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testClassifierCanMarkCertificateErrorAsTimeout(): void
    {
        $previous = new FakePsrNetworkException(
            'cURL error 60: SSL certificate problem',
            (new Psr17Factory())->createRequest('GET', 'https://example.test/me'),
        );
        $seen = null;
        $client = $this->createClient(
            new FakePsrClient(exception: $previous),
            static function (ClientExceptionInterface $e) use (&$seen): TransportErrorType {
                $seen = $e;

                return TransportErrorType::Timeout;
            },
        );

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидался таймаут');
        } catch (TimeoutException $e) {
            self::assertSame($previous, $seen);
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testClassifierCanMarkClientErrorAsTemporary(): void
    {
        $previous = new FakePsrClientException('Temporary transport failure');
        $client = $this->createClient(
            new FakePsrClient(exception: $previous),
            static function (ClientExceptionInterface $e) use ($previous): TransportErrorType {
                self::assertSame($previous, $e);

                return TransportErrorType::TemporaryNetwork;
            },
        );

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидалась временная ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testClassifierCanSuppressBuiltInTimeout(): void
    {
        $previous = new FakePsrNetworkException(
            'Connection timed out',
            (new Psr17Factory())->createRequest('GET', 'https://example.test/me'),
        );
        $client = $this->createClient(
            new FakePsrClient(exception: $previous),
            static function (ClientExceptionInterface $e) use ($previous): TransportErrorType {
                self::assertSame($previous, $e);

                return TransportErrorType::Permanent;
            },
        );

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидалась исходная ошибка');
        } catch (FakePsrNetworkException $e) {
            self::assertSame($previous, $e);
        }
    }

    public function testRequestBuildsPsrRequest(): void
    {
        $psrClient = new FakePsrClient(new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $client = $this->createClient($psrClient);

        $client->request(new HttpRequest(
            'POST',
            'https://example.test/messages?chat_id=1',
            ['Authorization' => 'token', 'Accept' => 'application/json'],
            body: new StringBody('{"text":"привет"}', 'application/json; charset=utf-8'),
        ));

        $psrRequest = $psrClient->lastRequest;
        self::assertInstanceOf(RequestInterface::class, $psrRequest);
        self::assertSame('POST', $psrRequest->getMethod());
        self::assertSame('https://example.test/messages?chat_id=1', (string) $psrRequest->getUri());
        self::assertSame('token', $psrRequest->getHeaderLine('Authorization'));
        self::assertSame('application/json', $psrRequest->getHeaderLine('Accept'));
        self::assertSame('application/json; charset=utf-8', $psrRequest->getHeaderLine('Content-Type'));
        self::assertSame('{"text":"привет"}', (string) $psrRequest->getBody());
    }

    /**
     * @param positive-int|null $lowSpeedLimit
     * @param positive-int|null $lowSpeedTime
     * @dataProvider lowSpeedLimits
     */
    public function testRequestIgnoresLowSpeedLimits(?int $lowSpeedLimit, ?int $lowSpeedTime): void
    {
        $psrClient = new FakePsrClient(new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $client = $this->createClient($psrClient);

        $response = $client->request(new HttpRequest(
            'GET',
            'https://example.test/me',
            lowSpeedLimit: $lowSpeedLimit,
            lowSpeedTime: $lowSpeedTime,
        ));

        self::assertSame(200, $response->getHttpCode());
        self::assertInstanceOf(RequestInterface::class, $psrClient->lastRequest);
    }

    public function testRequestRethrowsClientException(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $exception = new FakePsrClientException('Client is misconfigured');
        $client = $this->createClient(new FakePsrClient(exception: $exception));

        try {
            $client->request($request);
            self::fail('Ожидалась ошибка клиента');
        } catch (Throwable $e) {
            // Ошибка не сетевая, поэтому клиент отдаёт её как есть.
            self::assertSame($exception, $e);
        }
    }

    /**
     * @dataProvider permanentCurlMessages
     */
    public function testRequestRethrowsNonNetworkPermanentCurlError(string $message): void
    {
        $previous = new FakePsrClientException($message);
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидалась исходная ошибка');
        } catch (FakePsrClientException $e) {
            self::assertSame($previous, $e);
        }
    }

    public function testRequestRethrowsWhenOuterCurlErrorIsPermanent(): void
    {
        $previous = new FakePsrClientException(
            'cURL error 60: SSL certificate problem',
            0,
            new RuntimeException('cURL error 56: Recv failure: Connection reset by peer'),
        );
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидалась исходная ошибка');
        } catch (FakePsrClientException $e) {
            self::assertSame($previous, $e);
        }
    }

    public function testRequestReturnsResponse(): void
    {
        $psrResponse = new Response(
            404,
            ['Content-Type' => 'application/json', 'X-Request-Id' => ['abc']],
            '{"code":"chat.not.found"}',
        );
        $client = $this->createClient(new FakePsrClient($psrResponse));

        $response = $client->request(new HttpRequest('GET', 'https://example.test/chats'));

        self::assertSame(404, $response->getHttpCode());
        self::assertSame('application/json', $response->getContentType());
        self::assertSame('{"code":"chat.not.found"}', $response->getBody());
        self::assertSame(['abc'], $response->getHeaders()['X-Request-Id'] ?? null);
        self::assertSame('abc', $response->getFirstHeader('x-request-id'));
    }

    public function testRequestRewindsSeekableStreamBodyBeforeEachSend(): void
    {
        $stream = new FakeBodyStream('содержимое файла');
        $stream->read(4);
        $request = new HttpRequest(
            'POST',
            'https://example.test/upload',
            body: new FakeStreamBody($stream, 'application/octet-stream'),
        );
        $psrClient = new FakePsrClient(new Response(200));
        $client = $this->createClient($psrClient);

        $client->request($request);
        self::assertInstanceOf(RequestInterface::class, $psrClient->lastRequest);
        self::assertSame('содержимое файла', $psrClient->lastRequest->getBody()->getContents());

        $client->request($request);
        self::assertInstanceOf(RequestInterface::class, $psrClient->lastRequest);
        self::assertSame('содержимое файла', $psrClient->lastRequest->getBody()->getContents());
    }

    public function testRequestSendsStreamBody(): void
    {
        $psrClient = new FakePsrClient(new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $client = $this->createClient($psrClient);

        $client->request(new HttpRequest(
            'POST',
            'https://example.test/upload',
            body: new FakeStreamBody(new FakeBodyStream('содержимое файла'), 'application/octet-stream'),
        ));

        $psrRequest = $psrClient->lastRequest;
        self::assertInstanceOf(RequestInterface::class, $psrRequest);
        self::assertSame('application/octet-stream', $psrRequest->getHeaderLine('Content-Type'));

        $psrBody = $psrRequest->getBody();
        self::assertInstanceOf(PsrStream::class, $psrBody);
        self::assertSame(strlen('содержимое файла'), $psrBody->getSize());
        self::assertSame('содержимое файла', (string) $psrBody);
    }

    public function testRequestWithoutBodySendsEmptyBody(): void
    {
        $psrClient = new FakePsrClient(new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $client = $this->createClient($psrClient);

        $client->request(new HttpRequest('GET', 'https://example.test/me'));

        $psrRequest = $psrClient->lastRequest;
        self::assertInstanceOf(RequestInterface::class, $psrRequest);
        self::assertSame('', (string) $psrRequest->getBody());
        self::assertFalse($psrRequest->hasHeader('Content-Type'));
    }

    /**
     * @dataProvider networkErrorMessages
     */
    public function testRequestWrapsAnyNetworkError(string $message): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $previous = new FakePsrNetworkException(
            $message,
            (new Psr17Factory())->createRequest('GET', 'https://example.test/me'),
        );
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request($request);
            self::fail('Ожидалась временная ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertNotInstanceOf(TimeoutException::class, $e);
            self::assertSame($message, $e->getMessage());
            self::assertSame($request, $e->getRequest());
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testRequestWrapsCurlDnsError(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $factory = new Psr17Factory();
        $previous = new FakePsrNetworkException(
            'cURL error 6: Could not resolve host: example.test',
            $factory->createRequest('GET', 'https://example.test/me'),
        );
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request($request);
            self::fail('Ожидалась временная ошибка DNS');
        } catch (TemporaryNetworkException $e) {
            self::assertSame($previous, $e->getPrevious());
        }
    }

    /**
     * @dataProvider networkExceptionCodes
     */
    public function testRequestWrapsNetworkException(int $code): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $factory = new Psr17Factory();
        $previous = new FakePsrNetworkException(
            'cURL error 7: Connection refused',
            $factory->createRequest('GET', 'https://example.test/me'),
            $code,
        );
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request($request);
            self::fail('Ожидалась ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertNotInstanceOf(TimeoutException::class, $e);
            self::assertSame('cURL error 7: Connection refused', $e->getMessage());
            self::assertSame($request, $e->getRequest());
            self::assertSame($previous, $e->getPrevious());
            self::assertSame($code, $e->getCode());
        }
    }

    /**
     * @dataProvider temporaryCurlMessages
     */
    public function testRequestWrapsNonNetworkTemporaryCurlError(string $message): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $previous = new FakePsrClientException($message);
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request($request);
            self::fail('Ожидалась временная ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertNotInstanceOf(TimeoutException::class, $e);
            self::assertSame($message, $e->getMessage());
            self::assertSame($request, $e->getRequest());
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testRequestWrapsTemporaryCurlErrorFromPrevious(): void
    {
        $previous = new FakePsrClientException(
            'Request failed',
            0,
            new RuntimeException('cURL error 56: Recv failure: Connection reset by peer'),
        );
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидалась временная ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertNotInstanceOf(TimeoutException::class, $e);
            self::assertSame($previous, $e->getPrevious());
        }
    }

    /**
     * @dataProvider timeoutMessages
     */
    public function testRequestWrapsTimeoutException(string $message): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $factory = new Psr17Factory();
        $previous = new FakePsrNetworkException($message, $factory->createRequest('GET', 'https://example.test/me'));
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request($request);
            self::fail('Ожидалась ошибка по таймауту');
        } catch (TimeoutException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($request, $e->getRequest());
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testRequestWrapsTimeoutFromClientException(): void
    {
        $previous = new FakePsrClientException('Connection timed out');
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/me'));
            self::fail('Ожидался таймаут');
        } catch (TimeoutException $e) {
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testThrowsWithoutRequestFactory(): void
    {
        $this->expectException(LogicException::class);

        new PsrHttpClient($this->createMock(ClientInterface::class));
    }

    public function testThrowsWithoutStreamFactory(): void
    {
        $this->expectException(LogicException::class);

        new PsrHttpClient($this->createMock(ClientInterface::class), new Psr17Factory());
    }

    public function testTimeoutParameterInUrlIsNotTimeout(): void
    {
        $previous = new FakePsrNetworkException(
            'cURL error 60: SSL certificate problem: unable to get local issuer certificate'
            . ' (see https://curl.se/libcurl/c/libcurl-errors.html) for https://example.test/updates?timeout=30',
            (new Psr17Factory())->createRequest('GET', 'https://example.test/updates?timeout=30'),
        );
        $client = $this->createClient(new FakePsrClient(exception: $previous));

        try {
            $client->request(new HttpRequest('GET', 'https://example.test/updates?timeout=30'));
            self::fail('Ожидалась временная ошибка сети');
        } catch (TemporaryNetworkException $e) {
            self::assertNotInstanceOf(TimeoutException::class, $e);
            self::assertSame($previous, $e->getPrevious());
        }
    }

    public function testUsesClientAsFactoriesWhenNotGiven(): void
    {
        $psrClient = new FakePsrClientWithFactories(new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $client = new PsrHttpClient($psrClient);

        $response = $client->request(new HttpRequest(
            'POST',
            'https://example.test/me',
            body: new StringBody('{"text":"привет"}', 'application/json'),
        ));

        self::assertSame(200, $response->getHttpCode());
        self::assertInstanceOf(RequestInterface::class, $psrClient->lastRequest);
        self::assertSame('{"text":"привет"}', (string) $psrClient->lastRequest->getBody());
    }

    /**
     * @return iterable<string, array{string}> Тексты ошибок таймаута у популярных клиентов PSR-18.
     */
    public static function timeoutMessages(): iterable
    {
        yield 'guzzle: таймаут соединения' => [
            'cURL error 28: Connection timed out after 2000 milliseconds'
            . ' (see https://curl.se/libcurl/c/libcurl-errors.html) for http://192.0.2.1/me',
        ];
        yield 'guzzle: таймаут ответа' => [
            'cURL error 28: Operation timed out after 2000 milliseconds with 0 bytes received'
            . ' (see https://curl.se/libcurl/c/libcurl-errors.html) for http://192.0.2.1/me',
        ];
        yield 'symfony' => ['Operation timed out after 2000 milliseconds with 0 bytes received for "http://a/me".'];
        yield 'symfony: простой соединения' => ['Idle timeout reached for "http://a/updates?timeout=30".'];
        yield 'guzzle: пустой буфер ошибки curl' => [
            'cURL error 28: Timeout was reached'
            . ' (see https://curl.se/libcurl/c/libcurl-errors.html) for http://192.0.2.1/updates?timeout=30',
        ];
        yield 'php-http и buzz' => ['Connection timed out after 2000 milliseconds'];
        yield 'потоки php' => ['file_get_contents(http://a/me): Failed to open stream: Connection timed out'];
    }

    /**
     * @param (Closure(ClientExceptionInterface): ?TransportErrorType)|null $errorClassifier
     */
    private function createClient(FakePsrClient $psrClient, ?Closure $errorClassifier = null): PsrHttpClient
    {
        $factory = new Psr17Factory();

        return new PsrHttpClient($psrClient, $factory, $factory, $errorClassifier);
    }
}
