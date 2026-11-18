<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Contract\Http\HttpClientInterface;
use MaxMessenger\Bot\Contract\Http\HttpRequestInterface;
use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\Exception\Validation\MaximumException;
use MaxMessenger\Bot\Exception\Validation\MinimumException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryNetworkException;
use MaxMessenger\Bot\HttpClient\HttpRequest;
use MaxMessenger\Bot\HttpClient\HttpResponse;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\RawModel;
use MaxMessenger\Bot\Model\Response\Error;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;
use RuntimeException;
use Throwable;

use function json_encode;

use const CURLOPT_CAINFO;
use const JSON_UNESCAPED_UNICODE;

final class MaxApiClientTest extends Unit
{
    public function testAnswerOnCallbackWithDisableLinkPreview(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->answerOnCallback('callback-1', new NewMessageBody('Ответ'), true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/answers', $call['path']);
        self::assertSame(['callback_id' => 'callback-1', 'disable_link_preview' => 'true'], $call['query']);
    }

    public function testAnswerOnCallbackWithText(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->answerOnCallback('callback-1', 'Ответ');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/answers', $call['path']);
        self::assertSame(['callback_id' => 'callback-1'], $call['query']);
        self::assertSame(
            '{"message":{"text":"Ответ","notify":true}}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );
    }

    public function testAnswerOnCallbackWithoutDisableLinkPreview(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->answerOnCallback('callback-1', new NewMessageBody('Ответ'));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['callback_id' => 'callback-1'], $call['query']);
    }

    public function testConstructWithAccessTokenDoesNotUseRussianTrustedCaCertificatesByDefault(): void
    {
        $client = new MaxApiClient('test-token');

        $httpClient = $client->getMaxHttpClient()->getHttpClient();
        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        self::assertArrayNotHasKey(CURLOPT_CAINFO, $httpClient->getOptions());
    }

    public function testConstructWithAccessTokenUsesRussianTrustedCaCertificates(): void
    {
        $client = new MaxApiClient('test-token', true);

        $httpClient = $client->getMaxHttpClient()->getHttpClient();
        self::assertInstanceOf(CurlHttpClient::class, $httpClient);
        $caInfo = $httpClient->getOptions()[CURLOPT_CAINFO] ?? null;
        self::assertIsString($caInfo);
        self::assertStringEndsWith('/resources/certs/russian_trusted_ca_bundle.pem', $caInfo);
    }

    public function testDefaultHttpClientIsCreatedWithoutProvider(): void
    {
        $client = new MaxApiClient(new MaxApiConfig('test-token'));

        self::assertInstanceOf(MaxHttpClient::class, $client->getMaxHttpClient());
    }

    public function testDeleteAdminCallsDeleteAdmins(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->deleteAdmin(100, 200);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/chats/100/members/admins/200', $call['path']);
    }

    public function testDeleteComment(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $list = $client->getComments('mid.post', before: 20, after: 10, count: 5);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['count' => 5, 'before' => 20, 'after' => 10], $call['query']);

        $comments = $list->getMessages();
        self::assertCount(1, $comments);
        self::assertSame('mid.comment', $comments[0]->getBody()->getMid());
    }

    public function testGetCommentsByIdShortcut(): void
    {
        $http = new FakeMaxHttpClient(['messages' => [FakeMaxHttpClient::commentData()]]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

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
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->getComments('mid.post', ['mid.a', 'mid.b', 'mid.a']);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['comment_ids' => 'mid.a,mid.b'], $call['query']);
    }

    public function testGetCommentsFromPostShortcut(): void
    {
        $http = new FakeMaxHttpClient(['messages' => [FakeMaxHttpClient::commentData()]]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $list = $client->getCommentsFromPost('mid.post', before: 20, after: 10, count: 5);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages/mid.post/comments', $call['path']);
        self::assertSame(['count' => 5, 'before' => 20, 'after' => 10], $call['query']);
        self::assertCount(1, $list->getMessages());
    }

    public function testGetCommentsWithEqualTimeRange(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->getComments('mid.post', before: 10, after: 10);

        self::assertSame(['count' => 50, 'before' => 10, 'after' => 10], $http->lastCall()['query'] ?? null);
    }

    public function testGetCommentsWithInvalidTimeRange(): void
    {
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: new FakeMaxHttpClient(['messages' => []]));

        $this->expectException(MaximumException::class);

        $client->getComments('mid.post', before: 10, after: 20);
    }

    public function testGetMaxHttpClientReturnsProvidedClient(): void
    {
        $http = new FakeMaxHttpClient();
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        self::assertSame($http, $client->getMaxHttpClient());
    }

    public function testGetMessagesRejectsNegativeAfter(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $this->expectException(MinimumException::class);
        /** @psalm-suppress InvalidArgument */
        $client->getMessagesFromChat(100, after: -1);
    }

    public function testGetMessagesRejectsNegativeBefore(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $this->expectException(MinimumException::class);
        /** @psalm-suppress InvalidArgument */
        $client->getMessagesFromChat(100, before: -1);
    }

    public function testGetMyInfo(): void
    {
        $http = new FakeMaxHttpClient([
            'user_id' => 100,
            'first_name' => 'Бот',
            'is_bot' => true,
            'last_activity_time' => 1767225600000,
            'commands' => [['name' => 'help', 'description' => 'Помощь']],
        ]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $botInfo = $client->getMyInfo();

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/me', $call['path']);
        self::assertSame(100, $botInfo->getUserId());
        self::assertSame('Бот', $botInfo->getFirstName());
        self::assertTrue($botInfo->isBot());
        self::assertCount(1, $botInfo->getCommands() ?? []);
    }

    public function testGetUpdatesPassesQueryAndLongPollTimeout(): void
    {
        $http = new FakeMaxHttpClient(['updates' => [], 'marker' => 5]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        $client->getUpdates(
            limit: 10,
            timeout: 30,
            marker: 4,
            types: [UpdateType::MessageCreated, 'message_created', UpdateType::BotStarted],
        );

        self::assertSame(
            [
                'method' => 'getUpdates',
                'path' => '/updates',
                'body' => null,
                'query' => [
                    'limit' => 10,
                    'timeout' => 30,
                    'marker' => 4,
                    'types' => 'message_created,bot_started',
                ],
            ],
            $http->lastCall(),
        );
        self::assertSame(40000, $http->updatesTimeout);
    }

    public function testGetUpdatesWithInvalidTimeout(): void
    {
        $http = new FakeMaxHttpClient(['updates' => []]);
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);

        try {
            /** @psalm-suppress InvalidArgument */
            $client->getUpdates(timeout: -20);
            self::fail('Expected MinimumException.');
        } catch (MinimumException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testSendMessageDoesNotRetryWhenAttachmentRetriesAreDisabled(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $http->exceptions = [$this->createBadRequestException('attachment.not.ready')];
        $config = (new MaxApiConfig('test-token'))->setAttachmentRetryAttempts([]);
        $client = new MaxApiClient($config, httpClient: $http);

        try {
            $client->sendMessage(userId: 200, chatId: null, messageBody: 'Привет');
            self::fail('Ожидалась ошибка неготового вложения');
        } catch (BadRequestException $e) {
            self::assertSame('attachment.not.ready', $e->getError()?->getCode());
            self::assertCount(1, $http->calls);
        }
    }

    public function testSendMessageRetriesWhenAttachmentIsNotReady(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $exception = $this->createBadRequestException('attachment.not.ready');
        $http->exceptions = [$exception];
        $config = (new MaxApiConfig('test-token'))->setAttachmentRetryAttempts([1]);
        $client = new MaxApiClient($config, httpClient: $http);
        $logged = [];
        $client->setExceptionLogger(static function (string $method, Throwable $e) use (&$logged): void {
            $logged[] = [$method, $e];
        });

        $result = $client->sendMessage(userId: 200, chatId: null, messageBody: 'Привет');

        self::assertSame([[MaxApiClient::class . '::sendMessage', $exception]], $logged);
        self::assertCount(2, $http->calls);
        self::assertSame('/messages', $http->calls[1]['path']);
        self::assertSame('mid.out', $result->getMessage()->getBody()->getMid());
    }

    public function testSendMessageThrowsOnOtherBadRequest(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);
        $http->exceptions = [$this->createBadRequestException('chat.not.found')];
        $config = (new MaxApiConfig('test-token'))->setAttachmentRetryAttempts([1]);
        $client = new MaxApiClient($config, httpClient: $http);

        try {
            $client->sendMessage(userId: 200, chatId: null, messageBody: 'Привет');
            self::fail('Ожидалась ошибка запроса');
        } catch (BadRequestException $e) {
            self::assertSame('chat.not.found', $e->getError()?->getCode());
            self::assertCount(1, $http->calls);
        }
    }

    public function testSetExceptionLoggerPropagatesToProvidedClient(): void
    {
        $http = new FakeMaxHttpClient();
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);
        $logger = static function (string $_method, Throwable $_exception): void {};

        self::assertSame($client, $client->setExceptionLogger($logger));
        $propagatedLogger = $http->exceptionLogger;
        self::assertSame($logger, $propagatedLogger);

        self::assertSame($client, $client->setExceptionLogger(null));

        self::assertNull($http->exceptionLogger);
    }

    public function testSetExceptionLoggerReachesDefaultClient(): void
    {
        $attempts = 0;
        $logged = [];
        $transport = $this->createMock(HttpClientInterface::class);
        $transport
            ->expects(self::exactly(2))
            ->method('request')
            ->willReturnCallback(static function (HttpRequestInterface $request) use (&$attempts): HttpResponse {
                if (++$attempts === 1) {
                    throw new TemporaryNetworkException(new RuntimeException('Connection lost'), $request);
                }

                return new HttpResponse(200, 'application/json', '{"ok":true}');
            });
        $config = (new MaxApiConfig('test-token'))
            ->setHttpClient($transport)
            ->setRetryAttempts([1]);
        $client = new MaxApiClient($config);
        $client->setExceptionLogger(
            static function (string $method, Throwable $exception) use (&$logged): void {
                $logged[] = [$method, $exception];
            },
        );

        self::assertSame(['ok' => true], $client->getMaxHttpClient()->get('/test'));
        self::assertCount(1, $logged);
        self::assertInstanceOf(TemporaryNetworkException::class, $logged[0][1] ?? null);
    }

    /**
     * @param non-empty-string $code Код ошибки в ответе сервера.
     */
    private function createBadRequestException(string $code): BadRequestException
    {
        $error = ['code' => $code, 'message' => 'Ошибка запроса'];

        return new BadRequestException(
            new HttpRequest('POST', 'https://example.test/messages'),
            new HttpResponse(400, 'application/json', (string) json_encode($error)),
            Error::newFromData($error),
        );
    }
}
