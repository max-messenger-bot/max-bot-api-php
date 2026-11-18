<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\ModifyMembersError;
use MaxMessenger\Bot\Exception\RequiredArgumentException;
use MaxMessenger\Bot\Exception\RequiredArgumentsException;
use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\Exception\Validation\MatchException;
use MaxMessenger\Bot\Exception\Validation\MaximumException;
use MaxMessenger\Bot\Exception\Validation\MinimumException;
use MaxMessenger\Bot\Exception\Validation\MinItemsException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Enum\UploadType;
use MaxMessenger\Bot\Model\Request\ChatAdmin;
use MaxMessenger\Bot\Model\Request\ChatAdminsList;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\PinMessageBody;
use MaxMessenger\Bot\Model\Request\RawModel;
use MaxMessenger\Bot\Model\Request\SubscriptionRequestBody;
use MaxMessenger\Bot\Model\Request\UserIdsList;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\ModifyMembersResult;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxApiConfig;
use MaxMessenger\Bot\Tests\Support\Fake\FakeMaxHttpClient;

use function json_encode;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Запросы {@see MaxApiClient} к API через фейковый HTTP-клиент.
 */
final class MaxApiClientRequestsTest extends Unit
{
    public function testAddMembers(): void
    {
        $http = new FakeMaxHttpClient(['success' => true, 'failed_user_ids' => [3]]);

        $this->createClient($http)->addMembers(100, [1, 2]);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/chats/100/members', $call['path']);
        self::assertNull($call['query']);
        self::assertSame('{"user_ids":[1,2]}', json_encode($call['body']));
    }

    public function testAddMembersThrowsWithFullResult(): void
    {
        $data = [
            'success' => false,
            'code' => 'chat.denied',
            'message' => 'Access denied',
            'failed_user_ids' => [1, 2],
            'failed_user_details' => [['error_code' => 'add.participant.privacy', 'user_ids' => [1, 2]]],
            'extra' => ['value' => 123],
        ];
        $http = new FakeMaxHttpClient($data);

        try {
            $this->createClient($http)->addMembers(100, [1, 2]);
            self::fail('Ожидалась ошибка API');
        } catch (ModifyMembersError $exception) {
            $result = $exception->getResult();
            self::assertInstanceOf(ModifyMembersResult::class, $result);
            self::assertSame($data, $result->getRawData());
            self::assertSame([1, 2], $exception->getFailedUserIds());
            self::assertSame('chat.denied', $exception->getErrorCode());
            self::assertSame('Access denied', $exception->getMessage());
            $details = $exception->getFailedUserDetails();
            self::assertNotNull($details);
            self::assertSame('add.participant.privacy', $details[0]->getErrorCode());
        }
    }

    public function testAddMembersWithMissingSuccessThrowsModifyMembersError(): void
    {
        $http = new FakeMaxHttpClient(['failed_user_ids' => [1, 2]]);

        $this->expectException(ModifyMembersError::class);
        $this->expectExceptionMessage('The server did not return the message.');

        $this->createClient($http)->addMembers(100, [1, 2]);
    }

    public function testAddMembersWithModel(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->addMembers(100, UserIdsList::make([5]));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('{"user_ids":[5]}', json_encode($call['body']));
    }

    public function testAddMembersWithRawModel(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->addMembers(100, new RawModel(['user_ids' => [7]]));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('{"user_ids":[7]}', json_encode($call['body']));
    }

    public function testDeleteChat(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        /** @psalm-suppress DeprecatedMethod */
        $this->createClient($http)->deleteChat(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/chats/100', $call['path']);
        self::assertNull($call['query']);
    }

    public function testDeleteCommentWithCommentMessageBody(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $comment = CommentMessage::newFromData(FakeMaxHttpClient::commentData());

        $this->createClient($http)->deleteComment('mid.post', $comment->getBody());

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
    }

    public function testDeleteMessageWithInvalidId(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        try {
            $this->createClient($http)->deleteMessage('message-1');
            self::fail('Ожидалась ошибка формата идентификатора');
        } catch (MatchException $e) {
            self::assertStringContainsString('messageId', $e->getMessage());
            self::assertSame([], $http->calls);
        }
    }

    public function testEditCommentWithMessageAndCommentObjects(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $post = Message::newFromData(FakeMaxHttpClient::messageData());
        $comment = CommentMessage::newFromData(FakeMaxHttpClient::commentData());

        $this->createClient($http)->editComment($post, $comment, 'Новый текст');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('/messages/mid.out/comments', $call['path']);
        self::assertSame(['comment_id' => 'mid.comment'], $call['query']);
    }

    public function testEditMessage(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->editMessage('mid.1', 'Новый текст');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('put', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['message_id' => 'mid.1'], $call['query']);
        self::assertSame('{"text":"Новый текст","notify":true}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    public function testEditMessageWithMessageBodyObject(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $message = Message::newFromData(FakeMaxHttpClient::messageData());

        $this->createClient($http)->editMessage($message->getBody(), new NewMessageBody('Текст'));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['message_id' => 'mid.out'], $call['query']);
        self::assertSame('{"text":"Текст","notify":true}', json_encode($call['body'], JSON_UNESCAPED_UNICODE));
    }

    public function testEditMessageWithMessageObject(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);
        $message = Message::newFromData(FakeMaxHttpClient::messageData());

        $this->createClient($http)->editMessage($message, 'Текст');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['message_id' => 'mid.out'], $call['query']);
    }

    public function testGetAdmins(): void
    {
        $http = new FakeMaxHttpClient(['members' => [self::memberData()]]);

        $list = $this->createClient($http)->getAdmins(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/chats/100/members/admins', $call['path']);
        self::assertNull($call['query']);
        self::assertCount(1, $list->getMembers());
        self::assertTrue($list->getMembers()[0]->isAdmin());
    }

    public function testGetChat(): void
    {
        $http = new FakeMaxHttpClient([
            'chat_id' => 100,
            'type' => 'chat',
            'status' => 'active',
            'last_event_time' => 1_700_000_000_000,
            'participants_count' => 3,
            'is_public' => false,
        ]);

        $chat = $this->createClient($http)->getChat(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/chats/100', $call['path']);
        self::assertSame(100, $chat->getChatId());
        self::assertSame(3, $chat->getParticipantsCount());
    }

    public function testGetCommentsByIdWithInvalidId(): void
    {
        foreach ([null, '', 'invalid', 'mid.', 'mid.a,b', 'mid.a b'] as $commentId) {
            $http = new FakeMaxHttpClient(['messages' => []]);

            try {
                /** @psalm-suppress InvalidArgument */
                $this->createClient($http)->getCommentsById('mid.post', ['mid.valid', $commentId]);
                self::fail('Ожидалась ошибка формата идентификатора комментария');
            } catch (MatchException $e) {
                self::assertStringContainsString('commentIds[1]', $e->getMessage());
                self::assertSame([], $http->calls);
            }
        }
    }

    public function testGetMembers(): void
    {
        $http = new FakeMaxHttpClient(['members' => [self::memberData()], 'marker' => 42]);

        $list = $this->createClient($http)->getMembers(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/chats/100/members', $call['path']);
        self::assertSame(['count' => 20], $call['query']);
        self::assertSame(42, $list->getMarker());
        self::assertSame(200, $list->getMembers()[0]->getUserId());
    }

    public function testGetMembersByUserIds(): void
    {
        $http = new FakeMaxHttpClient(['members' => []]);

        $this->createClient($http)->getMembers(100, [1, 2, 1, 0], marker: 5, count: 10);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['user_ids' => '1,2'], $call['query']);
    }

    public function testGetMembersWithEmptyUserIds(): void
    {
        $http = new FakeMaxHttpClient(['members' => []]);

        try {
            $this->createClient($http)->getMembers(100, [0]);
            self::fail('Ожидалась ошибка пустого списка');
        } catch (MinItemsException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testGetMembersWithInvalidCount(): void
    {
        $http = new FakeMaxHttpClient(['members' => []]);

        try {
            /** @psalm-suppress InvalidArgument */
            $this->createClient($http)->getMembers(100, count: 101);
            self::fail('Ожидалась ошибка количества');
        } catch (MaximumException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testGetMembersWithMarker(): void
    {
        $http = new FakeMaxHttpClient(['members' => []]);

        $this->createClient($http)->getMembers(100, marker: 5, count: 10);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['count' => 10, 'marker' => 5], $call['query']);
    }

    public function testGetMembership(): void
    {
        $http = new FakeMaxHttpClient(self::memberData());

        $member = $this->createClient($http)->getMembership(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/chats/100/members/me', $call['path']);
        self::assertSame(200, $member->getUserId());
        self::assertSame([ChatAdminPermission::ReadAllMessages], $member->getPermissions());
    }

    public function testGetMessageById(): void
    {
        $http = new FakeMaxHttpClient(FakeMaxHttpClient::messageData());

        $message = $this->createClient($http)->getMessageById('mid.out');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages/mid.out', $call['path']);
        self::assertSame('ok', $message->getText());
    }

    public function testGetMessageByIdWithEmptyId(): void
    {
        $http = new FakeMaxHttpClient();

        try {
            /** @psalm-suppress InvalidArgument */
            $this->createClient($http)->getMessageById('');
            self::fail('Ожидалась ошибка пустого идентификатора');
        } catch (MatchException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testGetMessages(): void
    {
        $http = new FakeMaxHttpClient(['messages' => [FakeMaxHttpClient::messageData()]]);

        $list = $this->createClient($http)->getMessages(chatId: 100, before: 20, after: 10, count: 5);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 100, 'count' => 5, 'before' => 20, 'after' => 10], $call['query']);
        self::assertCount(1, $list->getMessages());
    }

    public function testGetMessagesById(): void
    {
        $http = new FakeMaxHttpClient(['messages' => [FakeMaxHttpClient::messageData()]]);

        $list = $this->createClient($http)->getMessagesById(['mid.a', 'mid.b', 'mid.a']);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['message_ids' => 'mid.a,mid.b'], $call['query']);
        self::assertSame('mid.out', $list->getMessages()[0]->getBody()->getMid());
    }

    public function testGetMessagesByIdWithEmptyList(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);

        try {
            /** @psalm-suppress InvalidArgument */
            $this->createClient($http)->getMessagesById([]);
            self::fail('Ожидалась ошибка пустого списка');
        } catch (MinItemsException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testGetMessagesByIdWithEmptyString(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);

        try {
            /** @psalm-suppress InvalidArgument */
            $this->createClient($http)->getMessagesById(['mid.valid', '']);
            self::fail('Ожидалась ошибка пустого идентификатора');
        } catch (MatchException $e) {
            self::assertStringContainsString('messageIds[1]', $e->getMessage());
            self::assertSame([], $http->calls);
        }
    }

    public function testGetMessagesByIdWithInvalidId(): void
    {
        foreach (['invalid', 'mid.', 'mid.a,b', 'mid.a b'] as $messageId) {
            $http = new FakeMaxHttpClient(['messages' => []]);

            try {
                $this->createClient($http)->getMessagesById(['mid.valid', $messageId]);
                self::fail('Ожидалась ошибка формата идентификатора');
            } catch (MatchException $e) {
                self::assertStringContainsString('messageIds[1]', $e->getMessage());
                self::assertSame([], $http->calls);
            }
        }
    }

    public function testGetMessagesByIdWithNullId(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);

        try {
            /** @psalm-suppress InvalidArgument */
            $this->createClient($http)->getMessagesById(['mid.valid', null]);
            self::fail('Ожидалась ошибка отсутствующего идентификатора');
        } catch (MatchException $e) {
            self::assertStringContainsString('messageIds[1]', $e->getMessage());
            self::assertSame([], $http->calls);
        }
    }

    public function testGetMessagesFromChat(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);

        $list = $this->createClient($http)->getMessagesFromChat(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 100, 'count' => 50], $call['query']);
        self::assertSame([], $list->getMessages());
    }

    public function testGetMessagesWithCountBoundaries(): void
    {
        foreach ([1, 100] as $count) {
            $http = new FakeMaxHttpClient(['messages' => []]);

            $this->createClient($http)->getMessagesFromChat(100, count: $count);

            self::assertSame(['chat_id' => 100, 'count' => $count], $http->lastCall()['query'] ?? null);
        }
    }

    public function testGetMessagesWithEqualTimeRange(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);

        $this->createClient($http)->getMessagesFromChat(100, before: 10, after: 10);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['chat_id' => 100, 'count' => 50, 'before' => 10, 'after' => 10], $call['query']);
    }

    public function testGetMessagesWithInvalidCount(): void
    {
        foreach ([0, 101] as $count) {
            $http = new FakeMaxHttpClient(['messages' => []]);

            try {
                /** @psalm-suppress InvalidArgument */
                $this->createClient($http)->getMessagesFromChat(100, count: $count);
                self::fail('Ожидалась ошибка количества');
            } catch (MinimumException|MaximumException) {
                self::assertSame([], $http->calls);
            }
        }
    }

    public function testGetMessagesWithInvalidTimeRange(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);

        try {
            $this->createClient($http)->getMessagesFromChat(100, before: 10, after: 20);
            self::fail('Ожидалась ошибка промежутка времени');
        } catch (MaximumException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testGetMessagesWithoutChatAndIds(): void
    {
        $http = new FakeMaxHttpClient(['messages' => []]);

        try {
            $this->createClient($http)->getMessages();
            self::fail('Ожидалась ошибка отсутствия чата');
        } catch (RequiredArgumentException $e) {
            self::assertStringContainsString('chatId', $e->getMessage());
            self::assertSame([], $http->calls);
        }
    }

    public function testGetPinnedMessage(): void
    {
        $http = new FakeMaxHttpClient(['message' => FakeMaxHttpClient::messageData()]);

        $result = $this->createClient($http)->getPinnedMessage(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/chats/100/pin', $call['path']);
        self::assertSame('mid.out', $result->getMessage()?->getBody()->getMid());
    }

    public function testGetPinnedMessageWhenNothingPinned(): void
    {
        $http = new FakeMaxHttpClient(['message' => null]);

        self::assertNull($this->createClient($http)->getPinnedMessage(100)->getMessage());
    }

    public function testGetSubscriptions(): void
    {
        $http = new FakeMaxHttpClient(['subscriptions' => [[
            'url' => 'https://example.test/webhook',
            'time' => 1_700_000_000_000,
            'update_types' => ['message_created'],
        ]]]);

        $result = $this->createClient($http)->getSubscriptions();

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/subscriptions', $call['path']);
        $subscriptions = $result->getSubscriptions();
        self::assertCount(1, $subscriptions);
        self::assertSame('https://example.test/webhook', $subscriptions[0]->getUrl());
        self::assertSame([UpdateType::MessageCreated], $subscriptions[0]->getUpdateTypes());
    }

    public function testGetUpdatesWithDefaultTimeout(): void
    {
        $http = new FakeMaxHttpClient(['updates' => [], 'marker' => 1]);

        $this->createClient($http)->getUpdates();

        self::assertSame(['limit' => 100, 'timeout' => 30], $http->lastCall()['query'] ?? null);
        self::assertSame(40000, $http->updatesTimeout);
    }

    public function testGetUploadUrl(): void
    {
        $http = new FakeMaxHttpClient(['url' => 'https://upload.example.test/1', 'token' => 'upload-token']);

        $endpoint = $this->createClient($http)->getUploadUrl(UploadType::Video);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/uploads', $call['path']);
        self::assertNull($call['body']);
        self::assertSame(['type' => 'video'], $call['query']);
        self::assertSame('https://upload.example.test/1', $endpoint->getUrl());
        self::assertSame('upload-token', $endpoint->getToken());
    }

    public function testGetVideoAttachmentDetails(): void
    {
        $http = new FakeMaxHttpClient([
            'token' => 'vid.abc',
            'width' => 1280,
            'height' => 720,
            'duration' => 30,
            'urls' => ['mp4_720' => 'https://video.example.test/720.mp4'],
        ]);

        $details = $this->createClient($http)->getVideoAttachmentDetails('vid.abc');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('get', $call['method']);
        self::assertSame('/videos/vid.abc', $call['path']);
        self::assertSame('vid.abc', $details->getToken());
        self::assertSame(1280, $details->getWidth());
        self::assertSame('https://video.example.test/720.mp4', $details->getUrls()?->getMp4R720());
    }

    public function testGetVideoAttachmentDetailsWithInvalidToken(): void
    {
        $http = new FakeMaxHttpClient();

        try {
            $this->createClient($http)->getVideoAttachmentDetails('abc');
            self::fail('Ожидалась ошибка формата токена');
        } catch (MatchException $e) {
            self::assertStringContainsString('videoToken', $e->getMessage());
            self::assertSame([], $http->calls);
        }
    }

    public function testGetVideoAttachmentDetailsWithReservedCharacters(): void
    {
        $videoToken = 'vid.a/b+c?d=e#f%g';
        $http = new FakeMaxHttpClient([
            'token' => $videoToken,
            'width' => 1280,
            'height' => 720,
            'duration' => 30,
        ]);

        $details = $this->createClient($http)->getVideoAttachmentDetails($videoToken);

        self::assertSame('/videos/vid.a%2Fb%2Bc%3Fd%3De%23f%25g', $http->lastCall()['path'] ?? null);
        self::assertSame($videoToken, $details->getToken());
    }

    public function testLeaveChat(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->leaveChat(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/chats/100/members/me', $call['path']);
    }

    public function testPinMessage(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->pinMessage(100, 'mid.1');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('put', $call['method']);
        self::assertSame('/chats/100/pin', $call['path']);
        self::assertSame('{"message_id":"mid.1","notify":true}', json_encode($call['body']));
    }

    public function testPinMessageWithModel(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->pinMessage(100, PinMessageBody::make('mid.1', notify: false));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('{"message_id":"mid.1","notify":false}', json_encode($call['body']));
    }

    public function testPostAdmins(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->postAdmins(100, [
            ChatAdmin::make(200, [ChatAdminPermission::ReadAllMessages, ChatAdminPermission::Write], 'Модератор'),
        ]);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/chats/100/members/admins', $call['path']);
        self::assertSame(
            '{"admins":[{"user_id":200,"permissions":["read_all_messages","write"],"alias":"Модератор"}]}',
            json_encode($call['body'], JSON_UNESCAPED_UNICODE),
        );
    }

    public function testPostAdminsWithModel(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->postAdmins(
            100,
            ChatAdminsList::make([ChatAdmin::make(200, [ChatAdminPermission::Write])]),
        );

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('{"admins":[{"user_id":200,"permissions":["write"]}]}', json_encode($call['body']));
    }

    public function testRemoveMember(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->removeMember(100, 200);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/chats/100/members', $call['path']);
        self::assertSame(['user_id' => 200, 'block' => 'false'], $call['query']);
    }

    public function testRemoveMemberWithBlock(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->removeMember(100, 200, block: true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['user_id' => 200, 'block' => 'true'], $call['query']);
    }

    public function testSendMessageToChatWithDisabledLinkPreview(): void
    {
        $http = new FakeMaxHttpClient();

        $this->createClient($http)->sendMessageToChat(100, 'https://example.test', disableLinkPreview: true);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/messages', $call['path']);
        self::assertSame(['chat_id' => 100, 'disable_link_preview' => 'true'], $call['query']);
        self::assertSame(
            '{"text":"https://example.test","notify":true}',
            json_encode($call['body'], JSON_UNESCAPED_SLASHES),
        );
    }

    public function testSendMessageToUser(): void
    {
        $http = new FakeMaxHttpClient();

        $result = $this->createClient($http)->sendMessageToUser(200, new NewMessageBody('Привет'));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame(['user_id' => 200], $call['query']);
        self::assertSame('mid.out', $result->getMessage()->getBody()->getMid());
    }

    public function testSendMessageWithBothRecipients(): void
    {
        $http = new FakeMaxHttpClient();

        try {
            $this->createClient($http)->sendMessage(200, 100, 'Привет');
            self::fail('Ожидалась ошибка двух получателей');
        } catch (RequiredArgumentsException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testSendMessageWithoutRecipient(): void
    {
        $http = new FakeMaxHttpClient();

        try {
            $this->createClient($http)->sendMessage(null, null, 'Привет');
            self::fail('Ожидалась ошибка отсутствия получателя');
        } catch (RequiredArgumentException $e) {
            self::assertStringContainsString('userId', $e->getMessage());
            self::assertSame([], $http->calls);
        }
    }

    public function testSimpleQueryErrorWithCode(): void
    {
        $http = new FakeMaxHttpClient([
            'success' => false,
            'code' => 'not.found',
            'message' => 'Resource not found',
        ]);

        try {
            $this->createClient($http)->leaveChat(100);
            self::fail('Ожидалась ошибка API');
        } catch (SimpleQueryError $exception) {
            self::assertSame('not.found', $exception->getErrorCode());
            self::assertSame('Resource not found', $exception->getMessage());
        }
    }

    public function testSimpleQueryErrorWithMessage(): void
    {
        $http = new FakeMaxHttpClient(['success' => false, 'message' => 'Chat not found']);

        $this->expectException(SimpleQueryError::class);
        $this->expectExceptionMessage('Chat not found');

        $this->createClient($http)->leaveChat(100);
    }

    public function testSimpleQueryErrorWithoutMessage(): void
    {
        $http = new FakeMaxHttpClient(['success' => false]);

        $this->expectException(SimpleQueryError::class);
        $this->expectExceptionMessage('The server did not return the message.');

        $this->createClient($http)->unpinMessage(100);
    }

    public function testSubscribe(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->subscribe(SubscriptionRequestBody::make(
            'https://example.test/webhook',
            secret: 'secret_1',
            update_types: [UpdateType::MessageCreated],
        ));

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('post', $call['method']);
        self::assertSame('/subscriptions', $call['path']);
        self::assertSame(
            '{"url":"https://example.test/webhook","secret":"secret_1","update_types":["message_created"]}',
            json_encode($call['body'], JSON_UNESCAPED_SLASHES),
        );
    }

    public function testUnpinMessage(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->unpinMessage(100);

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/chats/100/pin', $call['path']);
    }

    public function testUnsubscribe(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->unsubscribe('https://example.test/webhook');

        $call = $http->lastCall();
        self::assertNotNull($call);
        self::assertSame('delete', $call['method']);
        self::assertSame('/subscriptions', $call['path']);
        self::assertSame(['url' => 'https://example.test/webhook'], $call['query']);
    }

    public function testUnsubscribeAtMinimumUrlLength(): void
    {
        $http = new FakeMaxHttpClient(['success' => true]);

        $this->createClient($http)->unsubscribe('https://a.bc');

        self::assertSame(['url' => 'https://a.bc'], $http->lastCall()['query'] ?? null);
    }

    public function testUnsubscribeWithShortUrl(): void
    {
        foreach (['', 'https://a.b'] as $url) {
            $http = new FakeMaxHttpClient(['success' => true]);

            try {
                /** @psalm-suppress InvalidArgument */
                $this->createClient($http)->unsubscribe($url);
                self::fail('Ожидалась ошибка длины адреса подписки');
            } catch (MinLengthException $e) {
                self::assertStringContainsString('url', $e->getMessage());
                self::assertSame([], $http->calls);
            }
        }
    }

    private function createClient(FakeMaxHttpClient $http): MaxApiClient
    {
        return new MaxApiClient(new FakeMaxApiConfig(), httpClient: $http);
    }

    /**
     * @return array<string, mixed> Данные участника-администратора чата.
     */
    private static function memberData(): array
    {
        return [
            'user_id' => 200,
            'first_name' => 'Иван',
            'is_bot' => false,
            'last_activity_time' => 1_700_000_000_000,
            'last_access_time' => 1_700_000_000_000,
            'join_time' => 1_600_000_000_000,
            'is_owner' => false,
            'is_admin' => true,
            'permissions' => ['read_all_messages'],
        ];
    }
}
