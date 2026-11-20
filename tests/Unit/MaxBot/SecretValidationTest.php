<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Update\InvalidSecretException;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\MaxBot;
use Nyholm\Psr7\ServerRequest;

use function str_repeat;
use function strlen;

/**
 * Проверка длины секрета и сохранения проверки Webhook.
 */
final class SecretValidationTest extends Unit
{
    public function testConstructorValidatesSecret(): void
    {
        $this->expectException(MinLengthException::class);

        new MaxBot('test-token', '1234');
    }

    public function testInvalidSecretDoesNotReplaceExistingSecret(): void
    {
        $bot = new MaxBot('test-token', 'valid-secret');
        try {
            $bot->setSecret('1234');
            self::fail('Ожидалась ошибка длины секрета');
        } catch (MinLengthException) {
            $this->expectException(InvalidSecretException::class);
            $bot->readRequestContent(self::request());
        }
    }

    public function testNullDisablesSecretVerification(): void
    {
        $bot = new MaxBot('test-token', 'valid-secret');

        self::assertSame($bot, $bot->setSecret(null));
        self::assertSame('{}', $bot->readRequestContent(self::request()));
    }

    public function testSecretBoundariesAreAccepted(): void
    {
        $bot = new MaxBot('test-token');
        foreach ([5, 256] as $length) {
            $secret = str_repeat('s', $length);
            self::assertSame($bot, $bot->setSecret($secret));
            self::assertSame('{}', $bot->readRequestContent(self::request($secret)));
        }
    }

    public function testSecretTooLongIsRejected(): void
    {
        $this->expectException(MaxLengthException::class);

        (new MaxBot('test-token'))->setSecret(str_repeat('s', 257));
    }

    public function testSecretTooShortIsRejected(): void
    {
        foreach (['', '1234'] as $secret) {
            try {
                /** @psalm-suppress PossiblyInvalidArgument */
                (new MaxBot('test-token'))->setSecret($secret);
                self::fail('Ожидалась ошибка длины секрета');
            } catch (MinLengthException $exception) {
                self::assertStringContainsString('at least 5 characters', $exception->getMessage());
            }
        }
    }

    public function testValidateRequestHeadersAcceptsMatchingSecret(): void
    {
        $this->expectNotToPerformAssertions();
        $bot = new MaxBot('test-token', 'valid-secret');

        $bot->validateRequestHeaders('POST', 'application/json', '2', 'valid-secret');
    }

    public function testValidateRequestHeadersRejectsMismatchingSecret(): void
    {
        $bot = new MaxBot('test-token', 'valid-secret');

        $this->expectException(InvalidSecretException::class);

        $bot->validateRequestHeaders('POST', 'application/json', '2', 'other-secret');
    }

    public function testValidateRequestHeadersRejectsMissingSecret(): void
    {
        $bot = new MaxBot('test-token', 'valid-secret');

        $this->expectException(InvalidSecretException::class);

        $bot->validateRequestHeaders('POST', 'application/json', '2', '');
    }

    public function testValidateRequestHeadersSkipsSecretVerificationWhenDisabled(): void
    {
        $this->expectNotToPerformAssertions();
        $bot = new MaxBot('test-token');

        $bot->validateRequestHeaders('POST', 'application/json', '2', '');
        $bot->validateRequestHeaders('POST', 'application/json', '2', 'any-secret');
    }

    private static function request(?string $secret = null): ServerRequest
    {
        $headers = ['Content-Type' => 'application/json', 'Content-Length' => (string) strlen('{}')];
        if ($secret !== null) {
            $headers['X-Max-Bot-Api-Secret'] = $secret;
        }

        return new ServerRequest('POST', 'https://example.test/webhook', $headers, '{}');
    }
}
