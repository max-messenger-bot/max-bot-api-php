<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception\HttpResponse\Http;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxApiException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadGatewayException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\GatewayTimeoutException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\HttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\InternalHttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotAcceptableException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotAllowedException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotImplementedException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\OkException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\PartialUploadException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ProxyAuthenticationRequiredException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\RangeNotSatisfiableException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ServiceUnavailableException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\TooManyRequestsException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnauthorizedException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnknownException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnprocessableEntityException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnsupportedMediaTypeException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\HttpResponseException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\HttpResponse;
use MaxMessenger\Bot\Model\Response\Error;

final class HttpExceptionTest extends Unit
{
    /**
     * @return iterable<string, array{class-string<HttpException>, int, bool}>
     */
    public static function exceptions(): iterable
    {
        yield 'OkException' => [OkException::class, 200, false];
        yield 'PartialUploadException' => [PartialUploadException::class, 201, false];
        yield 'BadRequestException' => [BadRequestException::class, 400, false];
        yield 'UnauthorizedException' => [UnauthorizedException::class, 401, false];
        yield 'ForbiddenException' => [ForbiddenException::class, 403, false];
        yield 'NotFoundException' => [NotFoundException::class, 404, false];
        yield 'NotAllowedException' => [NotAllowedException::class, 405, false];
        yield 'NotAcceptableException' => [NotAcceptableException::class, 406, false];
        yield 'ProxyAuthenticationRequiredException' => [ProxyAuthenticationRequiredException::class, 407, false];
        yield 'UnsupportedMediaTypeException' => [UnsupportedMediaTypeException::class, 415, false];
        yield 'RangeNotSatisfiableException' => [RangeNotSatisfiableException::class, 416, false];
        yield 'UnknownException' => [UnknownException::class, 418, false];
        yield 'UnprocessableEntityException' => [UnprocessableEntityException::class, 422, false];
        yield 'TooManyRequestsException' => [TooManyRequestsException::class, 429, true];
        yield 'InternalHttpException' => [InternalHttpException::class, 500, true];
        yield 'NotImplementedException' => [NotImplementedException::class, 501, false];
        yield 'BadGatewayException' => [BadGatewayException::class, 502, true];
        yield 'ServiceUnavailableException' => [ServiceUnavailableException::class, 503, true];
        yield 'GatewayTimeoutException' => [GatewayTimeoutException::class, 504, true];
    }

    /**
     * @param class-string<HttpException> $class
     * @dataProvider exceptions
     */
    public function testConstructorKeepsRequestResponseAndError(string $class, int $httpCode): void
    {
        $request = $this->createRequest();
        $response = $this->createResponse($httpCode, '{"code":"x","message":"y"}');
        $error = $this->createError('chat.not.found', 'Chat not found');

        $exception = $this->createException($class, $request, $response, $error);

        self::assertSame('Chat not found', $exception->getMessage());
        self::assertSame($httpCode, $exception->getCode());
        self::assertSame($error, $exception->getError());
        self::assertSame($error, $exception->error);
        self::assertSame($request, $exception->getRequest());
        self::assertSame($response, $exception->getResponse());
        self::assertNull($exception->getPrevious());
    }

    /**
     * @param class-string<HttpException> $class
     * @dataProvider exceptions
     */
    public function testConstructorWithoutErrorUsesHttpCode(string $class, int $httpCode): void
    {
        $exception = $this->createException(
            $class,
            $this->createRequest(),
            $this->createResponse($httpCode, '<html><body>Server says no</body></html>'),
        );

        self::assertNull($exception->getError());
        self::assertSame('HTTP error ' . $httpCode, $exception->getMessage());
        self::assertSame('<html><body>Server says no</body></html>', $exception->getResponse()->getBody());
        self::assertSame($httpCode, $exception->getCode());
    }

    /**
     * @param class-string<HttpException> $class
     * @dataProvider exceptions
     */
    public function testHierarchy(string $class, int $httpCode, bool $isTemporary): void
    {
        $exception = $this->createException($class, $this->createRequest(), $this->createResponse($httpCode));

        self::assertInstanceOf(HttpException::class, $exception);
        self::assertInstanceOf(HttpResponseException::class, $exception);
        self::assertInstanceOf(MaxApiException::class, $exception);
        self::assertSame($isTemporary, $exception instanceof TemporaryExceptionInterface);
    }

    public function testThrowMapsHttpCodeToException(): void
    {
        $map = [
            200 => OkException::class,
            201 => PartialUploadException::class,
            400 => BadRequestException::class,
            401 => UnauthorizedException::class,
            403 => ForbiddenException::class,
            404 => NotFoundException::class,
            405 => NotAllowedException::class,
            406 => NotAcceptableException::class,
            407 => ProxyAuthenticationRequiredException::class,
            415 => UnsupportedMediaTypeException::class,
            416 => RangeNotSatisfiableException::class,
            422 => UnprocessableEntityException::class,
            429 => TooManyRequestsException::class,
            500 => InternalHttpException::class,
            501 => NotImplementedException::class,
            502 => BadGatewayException::class,
            503 => ServiceUnavailableException::class,
            504 => GatewayTimeoutException::class,
            418 => UnknownException::class,
        ];

        foreach ($map as $httpCode => $class) {
            try {
                HttpException::throw($this->createRequest(), $this->createResponse($httpCode), $this->createError());
                self::fail("Expected exception was not thrown for code $httpCode");
            } catch (HttpException $e) {
                self::assertInstanceOf($class, $e);
                self::assertSame($httpCode, $e->getCode());
            }
        }
    }

    private function createError(string $code = 'test.error', string $message = 'Test error message'): Error
    {
        return Error::newFromData([
            'code' => $code,
            'message' => $message,
        ]);
    }

    /**
     * @param class-string<HttpException> $class
     */
    private function createException(
        string $class,
        HttpRequest $request,
        HttpResponse $response,
        ?Error $error = null,
    ): HttpException {
        $exception = match ($class) {
            OkException::class => new OkException($request, $response, $error),
            PartialUploadException::class => new PartialUploadException($request, $response, $error),
            BadRequestException::class => new BadRequestException($request, $response, $error),
            UnauthorizedException::class => new UnauthorizedException($request, $response, $error),
            ForbiddenException::class => new ForbiddenException($request, $response, $error),
            NotFoundException::class => new NotFoundException($request, $response, $error),
            NotAllowedException::class => new NotAllowedException($request, $response, $error),
            NotAcceptableException::class => new NotAcceptableException($request, $response, $error),
            ProxyAuthenticationRequiredException::class => new ProxyAuthenticationRequiredException(
                $request,
                $response,
                $error,
            ),
            UnsupportedMediaTypeException::class => new UnsupportedMediaTypeException($request, $response, $error),
            RangeNotSatisfiableException::class => new RangeNotSatisfiableException($request, $response, $error),
            UnknownException::class => new UnknownException($request, $response, $error),
            UnprocessableEntityException::class => new UnprocessableEntityException($request, $response, $error),
            TooManyRequestsException::class => new TooManyRequestsException($request, $response, $error),
            InternalHttpException::class => new InternalHttpException($request, $response, $error),
            NotImplementedException::class => new NotImplementedException($request, $response, $error),
            BadGatewayException::class => new BadGatewayException($request, $response, $error),
            ServiceUnavailableException::class => new ServiceUnavailableException($request, $response, $error),
            GatewayTimeoutException::class => new GatewayTimeoutException($request, $response, $error),
        };
        self::assertInstanceOf($class, $exception);

        return $exception;
    }

    private function createRequest(): HttpRequest
    {
        return new HttpRequest('GET', 'https://example.test/messages');
    }

    private function createResponse(int $httpCode, string $body = '{}'): HttpResponse
    {
        return new HttpResponse($httpCode, 'application/json', $body);
    }
}
