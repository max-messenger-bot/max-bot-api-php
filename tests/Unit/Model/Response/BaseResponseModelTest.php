<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use JsonException;
use MaxMessenger\Bot\Exception\ActionProhibited;
use MaxMessenger\Bot\Model\Response\BaseResponseModel;
use MaxMessenger\Bot\Model\Response\BotAddedToChatUpdate;
use MaxMessenger\Bot\Model\Response\Button;
use MaxMessenger\Bot\Model\Response\CallbackButton;
use MaxMessenger\Bot\Model\Response\FailedUserDetails;
use MaxMessenger\Bot\Model\Response\UploadEndpoint;
use MaxMessenger\Bot\Model\Response\User;
use TypeError;

use function iterator_to_array;
use function json_encode;
use function serialize;
use function sprintf;
use function strlen;
use function unserialize;

use const JSON_UNESCAPED_SLASHES;

/**
 * Общие методы {@see BaseResponseModel} на примере конкретных моделей.
 */
final class BaseResponseModelTest extends Unit
{
    public function testArrayAccessRead(): void
    {
        $endpoint = UploadEndpoint::newFromData([
            'url' => 'https://upload.example.test',
            'token' => null,
        ]);

        self::assertTrue(isset($endpoint['url']));
        self::assertSame('https://upload.example.test', $endpoint['url']);
        self::assertFalse(isset($endpoint['token']));
        self::assertNull($endpoint['token']);
        self::assertFalse(isset($endpoint['missing']));
        self::assertNull($endpoint['missing']);
    }

    public function testGetIteratorAndRawData(): void
    {
        $data = ['url' => 'https://upload.example.test', 'token' => 'video-token'];
        $endpoint = UploadEndpoint::newFromData($data);

        self::assertSame($data, iterator_to_array($endpoint));
        self::assertSame($data, $endpoint->getRawData());
    }

    public function testJsonSerialize(): void
    {
        $endpoint = UploadEndpoint::newFromData(['url' => 'https://upload.example.test', 'token' => 'video-token']);

        self::assertSame(
            ['url' => 'https://upload.example.test', 'token' => 'video-token'],
            $endpoint->jsonSerialize(),
        );
        self::assertSame(
            '{"url":"https://upload.example.test","token":"video-token"}',
            json_encode($endpoint, JSON_UNESCAPED_SLASHES),
        );
    }

    public function testMakeNullableDateTime(): void
    {
        $user = User::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван',
            'is_bot' => false,
            'last_activity_time' => 1_700_000_000_123,
        ]);
        $userWithoutActivity = User::newFromData(['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false]);

        self::assertSame('2023-11-14 22:13:20.123 +00:00', $user->getLastActivityTime()?->format('Y-m-d H:i:s.v P'));
        self::assertNull($userWithoutActivity->getLastActivityTime());
    }

    public function testNewFromJson(): void
    {
        $endpoint = UploadEndpoint::newFromJson('{"url":"https://upload.example.test","token":"video-token"}');

        self::assertSame('https://upload.example.test', $endpoint->getUrl());
        self::assertSame('video-token', $endpoint->getRawData()['token']);
        self::assertSame([], UploadEndpoint::newFromJson('{}')->getRawData());
    }

    public function testNewFromJsonRejectsInvalidJson(): void
    {
        $this->expectException(JsonException::class);

        UploadEndpoint::newFromJson('{');
    }

    public function testNewFromJsonRejectsNull(): void
    {
        $this->expectException(TypeError::class);

        UploadEndpoint::newFromJson('null');
    }

    public function testNewFromJsonRejectsScalar(): void
    {
        $this->expectException(TypeError::class);

        UploadEndpoint::newFromJson('42');
    }

    public function testNewFromJsonSelectsChildClass(): void
    {
        $button = Button::newFromJson('{"type":"callback","text":"Кнопка","payload":"test"}');

        self::assertInstanceOf(CallbackButton::class, $button);
        self::assertSame('test', $button->getPayload());
    }

    public function testNewFromNullableData(): void
    {
        $endpoint = UploadEndpoint::newFromNullableData(['url' => 'https://upload.example.test']);

        self::assertNotNull($endpoint);
        self::assertSame('https://upload.example.test', $endpoint->getUrl());
        self::assertNull(UploadEndpoint::newFromNullableData(null));
    }

    public function testNewList2DFromData(): void
    {
        $rows = FailedUserDetails::newList2DFromData([
            [['error_code' => 'first', 'user_ids' => [1]], ['error_code' => 'second', 'user_ids' => [2]]],
            [],
            [['error_code' => 'third', 'user_ids' => [3]]],
        ]);

        self::assertCount(3, $rows);
        self::assertCount(2, $rows[0]);
        self::assertSame('second', $rows[0][1]->getErrorCode());
        self::assertSame([], $rows[1]);
        self::assertSame('third', $rows[2][0]->getErrorCode());
    }

    public function testNewListFromData(): void
    {
        $list = FailedUserDetails::newListFromData([
            'a' => ['error_code' => 'first', 'user_ids' => [1]],
            'b' => ['error_code' => 'second', 'user_ids' => [2]],
        ]);

        self::assertCount(2, $list);
        self::assertSame('first', $list[0]->getErrorCode());
        self::assertSame('second', $list[1]->getErrorCode());
        self::assertSame([], FailedUserDetails::newListFromData([]));
    }

    public function testNewListFromNullableData(): void
    {
        $list = FailedUserDetails::newListFromNullableData([['error_code' => 'first', 'user_ids' => [1]]]);

        self::assertNotNull($list);
        self::assertSame('first', $list[0]->getErrorCode());
        self::assertNull(FailedUserDetails::newListFromNullableData(null));
    }

    public function testOffsetSetIsProhibited(): void
    {
        $endpoint = UploadEndpoint::newFromData(['url' => 'https://upload.example.test']);

        $this->expectException(ActionProhibited::class);
        $this->expectExceptionMessage('Action prohibited.');

        $endpoint['url'] = 'https://other.example.test';
    }

    public function testOffsetUnsetIsProhibited(): void
    {
        $endpoint = UploadEndpoint::newFromData(['url' => 'https://upload.example.test']);

        $this->expectException(ActionProhibited::class);
        $this->expectExceptionMessage('Action prohibited.');

        unset($endpoint['url']);
    }

    public function testSerializationRoundTrip(): void
    {
        $data = [
            'update_type' => 'bot_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'is_channel' => false,
            'user' => ['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false],
        ];
        $update = BotAddedToChatUpdate::newFromData($data);
        $update->getUser();

        self::assertSame($data, $update->__serialize());

        $restored = unserialize(serialize($update));

        self::assertInstanceOf(BotAddedToChatUpdate::class, $restored);
        self::assertSame($data, $restored->getRawData());
        self::assertTrue($restored->isValid());
        self::assertSame('bot_added', $restored->getUpdateTypeRaw());
        self::assertSame(200, $restored->getUser()?->getUserId());
    }

    public function testUnserializeLegacyProperties(): void
    {
        $data = [
            'update_type' => 'bot_added',
            'timestamp' => 1_700_000_000_000,
            'chat_id' => 100,
            'is_channel' => false,
        ];
        $class = BotAddedToChatUpdate::class;
        $serialized = sprintf(
            'O:%d:"%s":2:{%s%s%sb:0;}',
            strlen($class),
            $class,
            serialize("\0*\0data"),
            serialize($data),
            serialize("\0" . $class . "\0user"),
        );
        $restored = unserialize($serialized);

        self::assertInstanceOf(BotAddedToChatUpdate::class, $restored);
        self::assertSame($data, $restored->getRawData());
        self::assertTrue($restored->isValid());
        self::assertSame('bot_added', $restored->getUpdateTypeRaw());
        self::assertSame(100, $restored->getChatId());
        self::assertNull($restored->getUser());
    }
}
