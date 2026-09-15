<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MustBeLessException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\RawModel;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

final class MaxApiClientTest extends Unit
{
    public function testAnswerOnCallbackWithDisableLinkPreview(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $client->answerOnCallback('callback-1', new NewMessageBody('Ответ'), true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/answers', $call['path']);
        self::assertSame(['callback_id' => 'callback-1', 'disable_link_preview' => 'true'], $call['query']);
    }

    public function testAnswerOnCallbackWithoutDisableLinkPreview(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $client->answerOnCallback('callback-1', new NewMessageBody('Ответ'));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['callback_id' => 'callback-1'], $call['query']);
    }

    public function testDeleteComment(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $client->deleteComment('mid.post', 'mid.comment');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
    }

    public function testEditChatWithDescription(): void
    {
        $http = new FakeMaxHttpClient([
            'chat_id' => 100,
            'type' => 'channel',
            'status' => 'active',
            'last_event_time' => 1_700_000_000_000,
            'participants_count' => 1,
            'is_public' => false,
        ]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $chat = $client->editChat(100, ChatPatch::make(description: 'Новое описание'));

        self::assertSame(100, $chat->getChatId());

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('patch', $call['method']);
        self::assertSame('/chats/100', $call['path']);
        self::assertSame(
            '{"description":"Новое описание","notify":true}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );
    }

    public function testEditComment(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $client->editComment('mid.post', 'mid.comment', 'Новый текст');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('put', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
        self::assertSame('{"text":"Новый текст"}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

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

    public function testGetCommentById(): void
    {
        $http = new FakeMaxHttpClient(FakeMaxHttpClient::commentData());
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $comment = $client->getCommentById('mid.post', 'mid.comment');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages/mid.post/comments/mid.comment', $call['path']);

        self::assertSame('mid.comment', $comment->getBody()->getMid());
        self::assertSame('комментарий', $comment->getText());
        self::assertSame('mid.post', $comment->getRecipient()->getPostId());
    }

    public function testGetComments(): void
    {
        $http = new FakeMaxHttpClient(['messages' => [FakeMaxHttpClient::commentData()]]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $list = $client->getComments('mid.post', before: 20, after: 10, count: 5);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['before' => 20, 'after' => 10, 'count' => 5], $call['query']);

        $comments = $list->getMessages();
        self::assertCount(1, $comments);
        self::assertSame('mid.comment', $comments[0]->getBody()->getMid());
    }

    public function testGetCommentsByIdShortcut(): void
    {
        $http = new FakeMaxHttpClient(['messages' => [FakeMaxHttpClient::commentData()]]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $list = $client->getCommentsById('mid.post', ['mid.a', 'mid.b']);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['comment_ids' => 'mid.a,mid.b'], $call['query']);
        self::assertCount(1, $list->getMessages());
    }

    public function testGetCommentsByIds(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $client->getComments('mid.post', ['mid.a', 'mid.b', 'mid.a']);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['comment_ids' => 'mid.a,mid.b'], $call['query']);
    }

    public function testGetCommentsFromPostShortcut(): void
    {
        $http = new FakeMaxHttpClient(['messages' => [FakeMaxHttpClient::commentData()]]);
        $client = new MaxApiClient(new FakeMaxApiConfig($http));

        $list = $client->getCommentsFromPost('mid.post', before: 20, after: 10, count: 5);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['before' => 20, 'after' => 10, 'count' => 5], $call['query']);
        self::assertCount(1, $list->getMessages());
    }

    public function testGetCommentsWithInvalidTimeRange(): void
    {
        $client = new MaxApiClient(new FakeMaxApiConfig(new FakeMaxHttpClient(['messages' => []])));

        $this->expectException(MustBeLessException::class);

        $client->getComments('mid.post', before: 10, after: 20);
    }
}
