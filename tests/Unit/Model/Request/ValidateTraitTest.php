<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\RequiredArgumentException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\InlineKeyboardAttachmentRequestPayload;
use MaxMessenger\Bot\Model\Request\ValidateTrait;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function array_diff_key;
use function array_fill;
use function str_repeat;

/**
 * Проверки трейта {@see ValidateTrait} через публичные методы моделей и клиента, которые его используют.
 */
final class ValidateTraitTest extends Unit
{
    public function testArray2DRejectsMissingRow(): void
    {
        $this->expectException(RequiredArgumentException::class);
        $this->expectExceptionMessage('The $buttons[0] argument cannot be null.');

        /** @psalm-suppress InvalidArgument */
        new InlineKeyboardAttachmentRequestPayload([null]);
    }

    public function testDisabledValidationIsSetPerClass(): void
    {
        BotCommand::$disableValidation = true;

        /** @psalm-suppress InvalidArgument */
        $command = new BotCommand(str_repeat('я', 65), '');

        self::assertSame(str_repeat('я', 65), $command->getName());
        self::assertSame('', $command->getDescription());

        $this->expectException(MinLengthException::class);

        /** @psalm-suppress InvalidArgument */
        ChatPatch::make(title: '');
    }

    public function testDisabledValidationSkipsArray2DCheck(): void
    {
        InlineKeyboardAttachmentRequestPayload::$disableValidation = true;

        /** @psalm-suppress InvalidArgument */
        $keyboard = new InlineKeyboardAttachmentRequestPayload([[]]);

        self::assertSame([[]], $keyboard->getButtons());
    }

    public function testDisabledValidationSkipsArrayCheck(): void
    {
        BotCommandsPatch::$disableValidation = true;

        $patch = BotCommandsPatch::make(array_fill(0, 33, new BotCommand('start')));

        self::assertCount(33, $patch->getCommands());
    }

    public function testDisabledValidationSkipsIntCheck(): void
    {
        MaxApiClient::$disableValidation = true;
        $http = new FakeMaxHttpClient(['updates' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        /** @psalm-suppress InvalidArgument */
        $client->getUpdates(limit: 0, timeout: 91);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['limit' => 0, 'timeout' => 91], $call['query']);
    }

    public function testDisabledValidationSkipsMidCheck(): void
    {
        MaxApiClient::$disableValidation = true;
        $http = new FakeMaxHttpClient(['messages' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->getMessagesById(['invalid']);

        self::assertSame(['message_ids' => 'invalid'], $http->lastCall()['query'] ?? null);
    }

    public function testDisabledValidationSkipsTimeRangeAndNotNullChecks(): void
    {
        MaxApiClient::$disableValidation = true;
        $http = new FakeMaxHttpClient(['messages' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->getMessages(before: 10, after: 20);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('/messages', $call['path']);
        $query = $call['query'];
        self::assertIsArray($query);
        self::assertArrayHasKey('chat_id', $query);
        self::assertSame(['count' => 50, 'before' => 10, 'after' => 20], array_diff_key($query, ['chat_id' => true]));
    }

    public function testStringLengthIsCountedInCharacters(): void
    {
        $name = str_repeat('я', 64);

        self::assertSame($name, BotCommand::make($name)->getName());
    }

    protected function tearDown(): void
    {
        BotCommand::$disableValidation = false;
        BotCommandsPatch::$disableValidation = false;
        InlineKeyboardAttachmentRequestPayload::$disableValidation = false;
        MaxApiClient::$disableValidation = false;

        parent::tearDown();
    }
}
