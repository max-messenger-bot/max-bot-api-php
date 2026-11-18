<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot\Event;

use Codeception\Test\Unit;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogClearedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogMutedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogUnmutedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\SendMessageToChatTrait;
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\UserRemovedFromChatEvent;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;
use function method_exists;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Методы трейта {@see SendMessageToChatTrait} во всех событиях, которые его используют, кроме событий сообщений:
 * их проверяет {@see MessageEventTraitTest}.
 */
final class SendMessageToChatTraitTest extends Unit
{
    /**
     * @return iterable<string, array{class-string<BaseEvent>, array<string, mixed>}>
     */
    public static function events(): iterable
    {
        $user = ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false];

        yield 'bot_added' => [
            BotAddedToChatEvent::class,
            [
                'update_type' => 'bot_added',
                'chat_id' => 100,
                'user' => $user,
                'is_channel' => false,
            ],
        ];
        yield 'bot_admin_permissions_changed' => [
            BotAdminPermissionsChangedEvent::class,
            [
                'update_type' => 'bot_admin_permissions_changed',
                'chat_id' => 100,
                'user_id' => 200,
                'bot_id' => 789,
                'is_channel' => false,
                'is_admin' => true,
            ],
        ];
        yield 'bot_started' => [
            BotStartedEvent::class,
            [
                'update_type' => 'bot_started',
                'chat_id' => 100,
                'user' => $user,
            ],
        ];
        yield 'chat_title_changed' => [
            ChatTitleChangedEvent::class,
            [
                'update_type' => 'chat_title_changed',
                'chat_id' => 100,
                'user' => $user,
                'title' => 'Новое название',
            ],
        ];
        yield 'dialog_cleared' => [
            DialogClearedEvent::class,
            [
                'update_type' => 'dialog_cleared',
                'chat_id' => 100,
                'user' => $user,
            ],
        ];
        yield 'dialog_muted' => [
            DialogMutedEvent::class,
            [
                'update_type' => 'dialog_muted',
                'chat_id' => 100,
                'user' => $user,
                'muted_until' => 1_700_000_500,
            ],
        ];
        yield 'dialog_unmuted' => [
            DialogUnmutedEvent::class,
            [
                'update_type' => 'dialog_unmuted',
                'chat_id' => 100,
                'user' => $user,
            ],
        ];
        yield 'message_removed' => [
            MessageRemovedEvent::class,
            [
                'update_type' => 'message_removed',
                'message_id' => 'mid.1',
                'chat_id' => 100,
                'user_id' => 200,
            ],
        ];
        yield 'user_added' => [
            UserAddedToChatEvent::class,
            [
                'update_type' => 'user_added',
                'chat_id' => 100,
                'user' => $user,
                'is_channel' => false,
            ],
        ];
        yield 'user_removed' => [
            UserRemovedFromChatEvent::class,
            [
                'update_type' => 'user_removed',
                'chat_id' => 100,
                'user' => $user,
                'is_channel' => false,
            ],
        ];
    }

    /**
     * @param class-string<BaseEvent> $class
     * @param array<string, mixed> $data
     * @dataProvider events
     */
    public function testSendActionToChat(string $class, array $data): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $event = $this->createEvent($http, $class, $data);
        self::assertTrue(method_exists($event, 'sendActionToChat'));

        $event->sendActionToChat(SenderAction::TypingOn);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/chats/100/actions', $call['path']);
        self::assertSame('{"action":"typing_on"}', json_encode($call['body']));
    }

    /**
     * @param class-string<BaseEvent> $class
     * @param array<string, mixed> $data
     * @dataProvider events
     */
    public function testSendMessageToChat(string $class, array $data): void
    {
        $http = new FakeMaxHttpClient();
        $event = $this->createEvent($http, $class, $data);
        self::assertTrue(method_exists($event, 'sendMessageToChat'));

        $result = $event->sendMessageToChat('В чат');

        self::assertInstanceOf(SendMessageResult::class, $result);
        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 100], $call['query']);
        self::assertSame('{"text":"В чат","notify":true}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param class-string<BaseEvent> $class
     * @param array<string, mixed> $data
     * @dataProvider events
     */
    public function testSendMessageToChatWithBodyAndDisabledLinkPreview(string $class, array $data): void
    {
        $http = new FakeMaxHttpClient();
        $event = $this->createEvent($http, $class, $data);
        self::assertTrue(method_exists($event, 'sendMessageToChat'));

        $event->sendMessageToChat(new NewMessageBody('https://example.test'), disableLinkPreview: true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['chat_id' => 100, 'disable_link_preview' => 'true'], $call['query']);
        self::assertSame('{"text":"https://example.test","notify":true}', json_encode(
            $call['body'],
            JSON_UNESCAPED_SLASHES,
        ));
    }

    /**
     * @param class-string<BaseEvent> $class
     * @param array<string, mixed> $data
     */
    private function createEvent(FakeMaxHttpClient $http, string $class, array $data): BaseEvent
    {
        $client = new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);
        $event = BaseEvent::new(Update::newFromData($data + ['timestamp' => 1_700_000_000_000]), $client, []);
        self::assertInstanceOf($class, $event);

        return $event;
    }
}
