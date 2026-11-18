<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxItemsException;
use MaxMessenger\Bot\Exception\Validation\MinItemsException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Request\UserIdsList;

use function json_encode;
use function range;

final class UserIdsListTest extends Unit
{
    public function testAddUserIdIgnoresDuplicate(): void
    {
        $list = UserIdsList::make([200]);

        $list->addUserId(200);

        self::assertSame([200], $list->getUserIds());
    }

    public function testAddUserIdIgnoresDuplicateInFullList(): void
    {
        $list = UserIdsList::make(range(1, 100));

        $list->addUserId(100);

        self::assertCount(100, $list->getUserIds());
    }

    public function testAddUserIdOverLimit(): void
    {
        $list = UserIdsList::make(range(1, 100));

        $this->expectException(MaxItemsException::class);
        $this->expectExceptionMessage(
            'Argument "userId" expects an array of up to 100 items, but an array of 101 items was passed.',
        );

        $list->addUserId(101);
    }

    public function testAddUserIdOverLimitWithDisabledValidation(): void
    {
        $list = UserIdsList::make(range(1, 100));
        UserIdsList::$disableValidation = true;

        try {
            $list->addUserId(101);
        } finally {
            UserIdsList::$disableValidation = false;
        }

        self::assertSame(range(1, 101), $list->getUserIds());
    }

    public function testAddUserIdToEmptyList(): void
    {
        $list = UserIdsList::new();

        $result = $list->addUserId(200);
        $list->addUserId(300);

        self::assertSame($list, $result);
        self::assertTrue($list->issetUserIds());
        self::assertSame([200, 300], $list->getUserIds());
    }

    public function testConstructor(): void
    {
        $list = new UserIdsList([200, 300]);

        self::assertTrue($list->issetUserIds());
        self::assertSame([200, 300], $list->getUserIds());
    }

    public function testEmptyListIsRejected(): void
    {
        $this->expectException(MinItemsException::class);
        $this->expectExceptionMessage(
            'Argument "userIds" expects a non-empty array, but an empty array was passed.',
        );

        /** @psalm-suppress InvalidArgument */
        UserIdsList::make([]);
    }

    public function testJsonSerialize(): void
    {
        self::assertSame('{"user_ids":[200,300]}', json_encode(UserIdsList::make([200, 300])));
    }

    public function testJsonSerializeWithoutUserIds(): void
    {
        $list = UserIdsList::new();

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "user_ids" field cannot be null.');

        $list->jsonSerialize();
    }

    public function testListOverLimit(): void
    {
        $this->expectException(MaxItemsException::class);
        $this->expectExceptionMessage(
            'Argument "userIds" expects an array of up to 100 items, but an array of 101 items was passed.',
        );

        UserIdsList::make(range(1, 101));
    }

    public function testNewWithoutUserIds(): void
    {
        self::assertFalse(UserIdsList::new()->issetUserIds());
    }

    public function testSetUserIdsRemovesDuplicatesAndReindexes(): void
    {
        $list = UserIdsList::new();

        $result = $list->setUserIds([3 => 200, 5 => 300, 8 => 200]);

        self::assertSame($list, $result);
        self::assertSame([200, 300], $list->getUserIds());
    }
}
