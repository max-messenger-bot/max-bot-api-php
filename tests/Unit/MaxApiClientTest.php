<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;
use MaxMessenger\Bot\Model\Request\RawModel;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class MaxApiClientTest extends Unit
{
    public function testEditMyCommands(): void
    {
        $http = new FakeMaxHttpClient([
            'commands' => [['name' => 'start', 'description' => 'Начать работу с ботом']],
        ]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $commandsInfo = $client->editMyCommands(
            BotCommandsPatch::make([new BotCommand('start', 'Начать работу с ботом')]),
        );

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('patch', $call['method']);
        self::assertSame('/me/commands', $call['path']);
        self::assertNull($call['query']);
        self::assertSame(
            '{"commands":[{"name":"start","description":"Начать работу с ботом"}]}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );

        $commands = $commandsInfo->getCommands();
        self::assertNotNull($commands);
        self::assertCount(1, $commands);
        self::assertSame('start', $commands[0]->getName());
    }

    public function testEditMyCommandsWithEmptyList(): void
    {
        $http = new FakeMaxHttpClient(['commands' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $commandsInfo = $client->editMyCommands(BotCommandsPatch::make([]));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('/me/commands', $call['path']);
        self::assertSame('{"commands":[]}', json_encode($call['body']));

        self::assertSame([], $commandsInfo->getCommands());
    }

    public function testEditMyCommandsWithRawModel(): void
    {
        $http = new FakeMaxHttpClient(['commands' => [['name' => 'help']]]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $commandsInfo = $client->editMyCommands(new RawModel(['commands' => [['name' => 'help']]]));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('patch', $call['method']);
        self::assertSame('/me/commands', $call['path']);
        self::assertSame('{"commands":[{"name":"help"}]}', json_encode($call['body']));

        $commands = $commandsInfo->getCommands();
        self::assertNotNull($commands);
        self::assertSame('help', $commands[0]->getName());
    }
}
