<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\ActionProhibited;
use MaxMessenger\Bot\Model\Response\BaseResponseModel;
use MaxMessenger\Bot\Model\Response\FailedUserDetails;
use MaxMessenger\Bot\Model\Response\UploadEndpoint;
use MaxMessenger\Bot\Model\Response\User;

use function iterator_to_array;
use function json_encode;

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
            '' => 'пусто',
        ]);

        self::assertTrue(isset($endpoint['url']));
        self::assertSame('https://upload.example.test', $endpoint['url']);
        self::assertFalse(isset($endpoint['token']));
        self::assertNull($endpoint['token']);
        self::assertFalse(isset($endpoint['missing']));
        self::assertNull($endpoint['missing']);
        self::assertTrue($endpoint->offsetExists(null));
        self::assertSame('пусто', $endpoint->offsetGet(null));
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
}
