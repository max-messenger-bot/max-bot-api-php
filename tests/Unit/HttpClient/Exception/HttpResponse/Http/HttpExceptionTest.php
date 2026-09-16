<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception\HttpResponse\Http;

use Codeception\Test\Unit;
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
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ProxyAuthenticationRequiredException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ServiceUnavailableException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\TooManyRequestsException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnauthorizedException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnknownException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnsupportedMediaTypeException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\HttpResponse;
use MaxMessenger\Bot\Model\Response\Error;

final class HttpExceptionTest extends Unit
{
    public function testThrowKeepsErrorRequestAndResponse(): void
    {
        $request = $this->createRequest();
        $response = $this->createResponse(404);
        $error = $this->createError('not.found', 'Chat not found');

        try {
            HttpException::throw($request, $response, $error);
            self::fail('Expected exception was not thrown');
        } catch (NotFoundException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame($request, $e->getRequest());
            self::assertSame($response, $e->getResponse());
            self::assertSame('Chat not found', $e->getMessage());
            self::assertSame(404, $e->getCode());
        }
    }

    public function testThrowMapsHttpCodeToException(): void
    {
        $map = [
            400 => BadRequestException::class,
            401 => UnauthorizedException::class,
            403 => ForbiddenException::class,
            404 => NotFoundException::class,
            405 => NotAllowedException::class,
            406 => NotAcceptableException::class,
            407 => ProxyAuthenticationRequiredException::class,
            415 => UnsupportedMediaTypeException::class,
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

    public function testThrowWithoutErrorUsesResponseBodyAsMessage(): void
    {
        $response = $this->createResponse(500, 'Internal Server Error');

        try {
            HttpException::throw($this->createRequest(), $response);
            self::fail('Expected exception was not thrown');
        } catch (InternalHttpException $e) {
            self::assertNull($e->getError());
            self::assertSame('Internal Server Error', $e->getMessage());
        }
    }

    private function createError(string $code = 'test.error', string $message = 'Test error message'): Error
    {
        return Error::newFromData([
            'code' => $code,
            'message' => $message,
        ]);
    }

    private function createRequest(): HttpRequest
    {
        return new HttpRequest('GET', 'https://example.test/messages');
    }

    private function createResponse(int $httpCode, string $body = '{}'): HttpResponse
    {
        return new HttpResponse($httpCode, 'https://example.test/messages', 'application/json', $body);
    }
}
