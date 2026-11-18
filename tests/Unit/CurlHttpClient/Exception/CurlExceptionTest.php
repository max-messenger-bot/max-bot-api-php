<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\CurlHttpClient\Exception;

use Codeception\Test\Unit;
use MaxMessenger\Bot\CurlHttpClient\Exception\CurlException;
use MaxMessenger\Bot\HttpClient\Exception\HttpClientException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use RuntimeException;

final class CurlExceptionTest extends Unit
{
    public function testKeepsCurlMessageAndCode(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');

        $exception
            = new CurlException('Connection timed out after 2000 milliseconds', CURLE_OPERATION_TIMEOUTED, $request);

        self::assertInstanceOf(HttpClientException::class, $exception);
        self::assertNotInstanceOf(TemporaryExceptionInterface::class, $exception);
        self::assertSame('Connection timed out after 2000 milliseconds', $exception->getMessage());
        self::assertSame(CURLE_OPERATION_TIMEOUTED, $exception->getCode());
        self::assertSame($request, $exception->getRequest());
        self::assertNull($exception->getPrevious());
    }

    public function testKeepsPrevious(): void
    {
        $request = new HttpRequest('GET', 'https://example.test/me');
        $previous = new RuntimeException('curl_init() failed');

        $exception = new CurlException('Could not initialize curl', 0, $request, $previous);

        self::assertSame($previous, $exception->getPrevious());
        self::assertSame($request, $exception->request);
        self::assertSame(0, $exception->getCode());
    }
}
