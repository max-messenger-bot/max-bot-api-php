<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Closure;
use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\Exception\MaxBot\Update\InvalidSecretException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;
use MaxMessenger\Bot\Tests\Support\Fake\FakePhpInputStream;
use MaxMessenger\Bot\Tests\Support\Fake\FakeServerRequest;

use function json_encode;
use function stream_wrapper_register;
use function stream_wrapper_restore;
use function stream_wrapper_unregister;
use function strlen;

/**
 * Чтение и обработка Webhook-запроса из объекта PSR-7.
 */
final class MaxBotRequestTest extends Unit
{
    public function testHandleFromGlobal(): void
    {
        $body = $this->updateJson();
        $bot = $this->createBot();
        $message = null;
        $bot->onMessageCreated(static function (MessageCreatedEvent $event) use (&$message): void {
            $message = $event->getMessage()->getText();
        });

        $handled = $this->withGlobalRequest($body, [], static fn(): bool => $bot->handleFromGlobal());

        self::assertTrue($handled);
        self::assertSame('Привет', $message);
    }

    public function testHandleFromRequest(): void
    {
        $handled = null;
        $bot = $this->createBot();
        $bot->onMessageCreated(function (MessageCreatedEvent $event) use (&$handled): bool {
            $handled = $event->getMessage()->getText();

            return true;
        });

        self::assertTrue($bot->handleFromRequest($this->createRequest($this->updateJson())));
        self::assertSame('Привет', $handled);
    }

    public function testReadRequestContent(): void
    {
        $body = $this->updateJson();

        self::assertSame($body, $this->createBot()->readRequestContent($this->createRequest($body)));
    }

    public function testReadRequestContentFromGlobal(): void
    {
        $body = $this->updateJson();
        $bot = $this->createBot();

        self::assertSame(
            $body,
            $this->withGlobalRequest($body, [], static fn(): string => $bot->readRequestContentFromGlobal()),
        );
    }

    public function testReadRequestContentFromGlobalWithEmptyBody(): void
    {
        $bot = $this->createBot();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Body is empty.');

        $this->withGlobalRequest(
            '',
            ['CONTENT_LENGTH' => '0'],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        );
    }

    public function testReadRequestContentFromGlobalWithGetMethod(): void
    {
        $bot = $this->createBot();

        $this->expectException(BadRequestException::class);

        $this->withGlobalRequest(
            $this->updateJson(),
            ['REQUEST_METHOD' => 'GET'],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        );
    }

    public function testReadRequestContentFromGlobalWithInvalidSecret(): void
    {
        $bot = $this->createBot()->setSecret('s3cret');

        $this->expectException(InvalidSecretException::class);

        $this->withGlobalRequest(
            $this->updateJson(),
            ['HTTP_X_MAX_BOT_API_SECRET' => 'other'],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        );
    }

    public function testReadRequestContentFromGlobalWithNonNumericContentLength(): void
    {
        $bot = $this->createBot();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Content-Length must be a non-negative integer.');

        $this->withGlobalRequest(
            '{}',
            ['CONTENT_LENGTH' => '2x'],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        );
    }

    public function testReadRequestContentFromGlobalWithSecret(): void
    {
        $body = $this->updateJson();
        $bot = $this->createBot()->setSecret('s3cret');

        self::assertSame($body, $this->withGlobalRequest(
            $body,
            ['HTTP_X_MAX_BOT_API_SECRET' => 's3cret'],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        ));
    }

    public function testReadRequestContentFromGlobalWithWrongContentLength(): void
    {
        $bot = $this->createBot();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The Body size does not match the passed content-length.');

        $this->withGlobalRequest(
            $this->updateJson(),
            ['CONTENT_LENGTH' => '1'],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        );
    }

    public function testReadRequestContentFromGlobalWithoutContentLength(): void
    {
        $bot = $this->createBot();

        $this->expectException(BadRequestException::class);

        $this->withGlobalRequest(
            $this->updateJson(),
            ['CONTENT_LENGTH' => null],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        );
    }

    public function testReadRequestContentFromGlobalWithoutJsonContentType(): void
    {
        $bot = $this->createBot();

        $this->expectException(BadRequestException::class);

        $this->withGlobalRequest(
            $this->updateJson(),
            ['CONTENT_TYPE' => 'text/plain'],
            static fn(): string => $bot->readRequestContentFromGlobal(),
        );
    }

    public function testReadRequestContentWithEmptyBody(): void
    {
        $request = new FakeServerRequest('POST', '', [
            'Content-Type' => 'application/json',
            'Content-Length' => '0',
        ]);

        $this->expectException(BadRequestException::class);

        $this->createBot()->readRequestContent($request);
    }

    public function testReadRequestContentWithGetMethod(): void
    {
        $request = $this->createRequest($this->updateJson(), method: 'GET');

        $this->expectException(BadRequestException::class);

        $this->createBot()->readRequestContent($request);
    }

    public function testReadRequestContentWithInvalidSecret(): void
    {
        $bot = $this->createBot()->setSecret('s3cret');
        $request = $this->createRequest($this->updateJson(), ['X-Max-Bot-Api-Secret' => 'other']);

        $this->expectException(InvalidSecretException::class);

        $bot->readRequestContent($request);
    }

    public function testReadRequestContentWithNonNumericContentLength(): void
    {
        $request = new FakeServerRequest('POST', '{}', [
            'Content-Type' => 'application/json',
            'Content-Length' => '2x',
        ]);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Content-Length must be a non-negative integer.');

        $this->createBot()->readRequestContent($request);
    }

    public function testReadRequestContentWithSecret(): void
    {
        $body = $this->updateJson();
        $bot = $this->createBot()->setSecret('s3cret');
        $request = $this->createRequest($body, ['X-Max-Bot-Api-Secret' => 's3cret']);

        self::assertSame($body, $bot->readRequestContent($request));
    }

    public function testReadRequestContentWithWrongContentLength(): void
    {
        $request = new FakeServerRequest('POST', $this->updateJson(), [
            'Content-Type' => 'application/json',
            'Content-Length' => '1',
        ]);

        $this->expectException(BadRequestException::class);

        $this->createBot()->readRequestContent($request);
    }

    public function testReadRequestContentWithoutContentLength(): void
    {
        $request = new FakeServerRequest('POST', $this->updateJson(), [
            'Content-Type' => 'application/json',
        ]);

        $this->expectException(BadRequestException::class);

        $this->createBot()->readRequestContent($request);
    }

    public function testReadRequestContentWithoutJsonContentType(): void
    {
        $body = $this->updateJson();
        $request = new FakeServerRequest('POST', $body, [
            'Content-Type' => 'text/plain',
            'Content-Length' => (string) strlen($body),
        ]);

        $this->expectException(BadRequestException::class);

        $this->createBot()->readRequestContent($request);
    }

    private function createBot(): MaxBot
    {
        return new MaxBot(new MaxApiClient(new FakeMaxApiConfig(), httpClient: new FakeMaxHttpClient()));
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(string $body, array $headers = [], string $method = 'POST'): FakeServerRequest
    {
        return new FakeServerRequest(
            $method,
            $body,
            $headers + [
                'Content-Type' => 'application/json',
                'Content-Length' => (string) strlen($body),
            ],
        );
    }

    private function updateJson(): string
    {
        return (string) json_encode([
            'update_type' => 'message_created',
            'timestamp' => 1_700_000_000_000,
            'message' => [
                'sender' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
                'recipient' => ['chat_id' => 100, 'chat_type' => 'dialog', 'user_id' => 200],
                'timestamp' => 1_700_000_000_000,
                'body' => ['mid' => 'mid.1', 'seq' => 1, 'text' => 'Привет'],
            ],
        ]);
    }

    /**
     * Выполняет код с подменёнными `$_SERVER` и `php://input` и восстанавливает их после.
     *
     * @template T
     * @param string $body Тело запроса в `php://input`.
     * @param array<string, string|null> $server Изменения `$_SERVER`: `null` удаляет ключ.
     * @param Closure(): T $callback Код, читающий запрос.
     * @return T Результат `$callback`.
     */
    private function withGlobalRequest(string $body, array $server, Closure $callback): mixed
    {
        $savedServer = $_SERVER;
        $server += [
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json',
            'CONTENT_LENGTH' => (string) strlen($body),
        ];
        foreach ($server as $key => $value) {
            if ($value === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $value;
            }
        }
        FakePhpInputStream::$body = $body;

        self::assertTrue(stream_wrapper_unregister('php'));
        try {
            self::assertTrue(stream_wrapper_register('php', FakePhpInputStream::class));

            return $callback();
        } finally {
            $restored = stream_wrapper_restore('php');
            FakePhpInputStream::$body = '';
            $_SERVER = $savedServer;
            self::assertTrue($restored);
        }
    }
}
