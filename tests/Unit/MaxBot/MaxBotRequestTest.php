<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\Exception\MaxBot\Update\InvalidSecretException;
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;
use MaxMessenger\Bot\Tests\Support\Fake\FakeServerRequest;

use function json_encode;
use function strlen;

/**
 * Чтение и обработка Webhook-запроса из объекта PSR-7.
 */
final class MaxBotRequestTest extends Unit
{
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
        return new MaxBot(new FakeMaxApiConfig(new FakeMaxHttpClient()));
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
}
