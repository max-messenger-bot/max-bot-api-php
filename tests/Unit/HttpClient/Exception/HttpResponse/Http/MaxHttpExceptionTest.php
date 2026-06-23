<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\HttpClient\Exception\HttpResponse\Http;

use Codeception\Test\Unit;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\InternalHttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\MaxHttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotAllowedException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ServiceUnavailableException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\TooManyRequestsException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnauthorizedException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnknownException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnsupportedMediaTypeException;
use MaxMessenger\Bot\Model\Response\Error;
use Mj4444\SimpleHttpClient\Contracts\HttpResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class MaxHttpExceptionTest extends Unit
{
    public function testBadRequestAttachmentNotReadyByCode(): void
    {
        $response = $this->createMockResponse(400);
        $error = $this->createError('attachment.not.ready', 'Attachment not ready');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (BadRequestException $e) {
            self::assertTrue($e->isAttachmentNotReady());
        }
    }

    public function testBadRequestAttachmentNotReadyByMessage(): void
    {
        $response = $this->createMockResponse(400);
        $error = $this->createError('some.other.code', 'attachment.file.not.processed: File is still processing');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (BadRequestException $e) {
            self::assertTrue($e->isAttachmentNotReady());
        }
    }

    public function testBadRequestAttachmentReady(): void
    {
        $response = $this->createMockResponse(400);
        $error = $this->createError('other.error', 'Some other error');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (BadRequestException $e) {
            self::assertFalse($e->isAttachmentNotReady());
        }
    }

    public function testGetError(): void
    {
        $response = $this->createMockResponse(400);
        $error = $this->createError('test.code', 'Test Message');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (MaxHttpException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('test.code', $e->getError()->getCode());
            self::assertSame('Test Message', $e->getError()->getMessage());
        }
    }

    public function testThrowMaxBadRequest(): void
    {
        $response = $this->createMockResponse(400);
        $error = $this->createError('bad.request', 'Bad Request');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (BadRequestException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Bad Request', $e->getMessage());
            self::assertSame(400, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxForbidden(): void
    {
        $response = $this->createMockResponse(403);
        $error = $this->createError('forbidden', 'Forbidden');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (ForbiddenException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Forbidden', $e->getMessage());
            self::assertSame(403, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxInternalError(): void
    {
        $response = $this->createMockResponse(500);
        $error = $this->createError('internal.error', 'Internal Server Error');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (InternalHttpException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Internal Server Error', $e->getMessage());
            self::assertSame(500, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxNotAllowed(): void
    {
        $response = $this->createMockResponse(405);
        $error = $this->createError('not.allowed', 'Method Not Allowed');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (NotAllowedException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Method Not Allowed', $e->getMessage());
            self::assertSame(405, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxNotFound(): void
    {
        $response = $this->createMockResponse(404);
        $error = $this->createError('not.found', 'Not Found');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (NotFoundException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Not Found', $e->getMessage());
            self::assertSame(404, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxServiceUnavailable(): void
    {
        $response = $this->createMockResponse(503);
        $error = $this->createError('service.unavailable', 'Service Unavailable');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (ServiceUnavailableException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Service Unavailable', $e->getMessage());
            self::assertSame(503, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxTooManyRequests(): void
    {
        $response = $this->createMockResponse(429);
        $error = $this->createError('too.many.requests', 'Too Many Request');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (TooManyRequestsException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Too Many Request', $e->getMessage());
            self::assertSame(429, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxUnauthorized(): void
    {
        $response = $this->createMockResponse(401);
        $error = $this->createError('unauthorized', 'Unauthorized');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (UnauthorizedException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Unauthorized', $e->getMessage());
            self::assertSame(401, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxUnknown(): void
    {
        $response = $this->createMockResponse(599);
        $error = $this->createError('unknown.error', 'Unknown Error');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (UnknownException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Unknown Error', $e->getMessage());
            self::assertSame(599, $e->getCode());
            self::assertSame($response, $e->getResponse());
        }
    }

    public function testThrowMaxUnsupportedMediaType(): void
    {
        $response = $this->createMockResponse(415);
        $error = $this->createError('unsupported.media.type', 'Unsupported Media Type');

        try {
            MaxHttpException::throwMax($response, $error);
            self::fail('Expected exception was not thrown');
        } catch (UnsupportedMediaTypeException $e) {
            self::assertSame($error, $e->getError());
            self::assertSame('Unsupported Media Type', $e->getMessage());
            self::assertSame(415, $e->getCode());
            self::assertSame($response, $e->getResponse());
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
     * @psalm-return HttpResponseInterface&MockObject
     */
    private function createMockResponse(int $httpCode): HttpResponseInterface
    {
        $mock = $this->createMock(HttpResponseInterface::class);
        $mock->method('getHttpCode')->willReturn($httpCode);

        return $mock;
    }
}
