<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\SubscriptionRequestBody;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_SLASHES;

final class SubscriptionRequestBodyTest extends Unit
{
    public function testConstructor(): void
    {
        $body = new SubscriptionRequestBody(
            'https://example.test/hook',
            'secret_1-A',
            [UpdateType::MessageCreated],
            '0.0.10',
        );

        self::assertTrue($body->issetUrl());
        self::assertTrue($body->issetSecret());
        self::assertTrue($body->issetUpdateTypes());
        self::assertTrue($body->issetVersion());
        self::assertSame('https://example.test/hook', $body->getUrl());
        self::assertSame('secret_1-A', $body->getSecret());
        self::assertSame([UpdateType::MessageCreated], $body->getUpdateTypes());
        self::assertSame('0.0.10', $body->getVersion());
    }

    public function testInvalidSecretIsRejected(): void
    {
        $this->expectException(MatchException::class);
        $this->expectExceptionMessage('Argument "secret" has an invalid value.');

        SubscriptionRequestBody::make('https://example.test/hook', 'secret with spaces');
    }

    public function testInvalidVersionIsRejected(): void
    {
        $this->expectException(MatchException::class);
        $this->expectExceptionMessage('Argument "version" has an invalid value.');

        SubscriptionRequestBody::make('https://example.test/hook', version: '1.2');
    }

    public function testJsonSerialize(): void
    {
        $body = SubscriptionRequestBody::make(
            'https://example.test/hook',
            'secret_1',
            [UpdateType::MessageCreated, UpdateType::BotStarted],
            '0.0.10',
        );

        self::assertSame(
            '{"url":"https://example.test/hook","secret":"secret_1",'
            . '"update_types":["message_created","bot_started"],"version":"0.0.10"}',
            json_encode($body, JSON_UNESCAPED_SLASHES),
        );
    }

    public function testJsonSerializeWithoutUrl(): void
    {
        $body = SubscriptionRequestBody::new(secret: 'secret_1');

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "url" field cannot be null.');

        $body->jsonSerialize();
    }

    public function testNewWithoutArguments(): void
    {
        $body = SubscriptionRequestBody::new();

        self::assertFalse($body->issetUrl());
        self::assertFalse($body->issetSecret());
        self::assertFalse($body->issetUpdateTypes());
        self::assertFalse($body->issetVersion());
        self::assertNull($body->getSecret());
        self::assertNull($body->getUpdateTypes());
        self::assertNull($body->getVersion());
    }

    public function testSecretAtLengthLimits(): void
    {
        $longSecret = str_repeat('a', 256);

        self::assertSame('abcde', SubscriptionRequestBody::make('https://example.test/hook', 'abcde')->getSecret());
        self::assertSame(
            $longSecret,
            SubscriptionRequestBody::make('https://example.test/hook', $longSecret)->getSecret(),
        );
    }

    public function testSecretTooLong(): void
    {
        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "secret" expects a string of up to 256 characters, but a string of 257 characters was passed.',
        );

        SubscriptionRequestBody::make('https://example.test/hook', str_repeat('a', 257));
    }

    public function testSecretTooShort(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "secret" expects a string of at least 5 characters, but a string of 4 characters was passed.',
        );

        SubscriptionRequestBody::make('https://example.test/hook', 'abcd');
    }

    public function testSetUpdateTypesReindexesList(): void
    {
        $body = SubscriptionRequestBody::new();

        $result = $body->setUpdateTypes([3 => UpdateType::BotStarted, 9 => UpdateType::MessageCallback]);

        self::assertSame($body, $result);
        self::assertSame([UpdateType::BotStarted, UpdateType::MessageCallback], $body->getUpdateTypes());
    }

    public function testSetUpdateTypesRemovesDuplicates(): void
    {
        $body = SubscriptionRequestBody::new()->setUpdateTypes([
            UpdateType::BotStarted,
            UpdateType::MessageCreated,
            UpdateType::BotStarted,
        ]);

        self::assertSame([UpdateType::BotStarted, UpdateType::MessageCreated], $body->getUpdateTypes());
    }

    public function testSetters(): void
    {
        $body = SubscriptionRequestBody::new();

        self::assertSame($body, $body->setUrl('https://example.test/hook'));
        self::assertSame($body, $body->setSecret('secret_1'));
        self::assertSame($body, $body->setVersion('1.2.3'));
        self::assertSame('https://example.test/hook', $body->getUrl());
        self::assertSame('secret_1', $body->getSecret());
        self::assertSame('1.2.3', $body->getVersion());
    }

    public function testUnsetters(): void
    {
        $body = SubscriptionRequestBody::make(
            'https://example.test/hook',
            'secret_1',
            [UpdateType::MessageCreated],
            '0.0.10',
        );

        self::assertSame($body, $body->unsetSecret());
        self::assertSame($body, $body->unsetUpdateTypes());
        self::assertSame($body, $body->unsetVersion());
        self::assertFalse($body->issetSecret());
        self::assertFalse($body->issetUpdateTypes());
        self::assertFalse($body->issetVersion());
        self::assertSame('{"url":"https://example.test/hook"}', json_encode($body, JSON_UNESCAPED_SLASHES));
    }

    public function testUrlTooShort(): void
    {
        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage(
            'Argument "url" expects a string of at least 12 characters, but a string of 11 characters was passed.',
        );

        SubscriptionRequestBody::make('https://a.b');
    }
}
