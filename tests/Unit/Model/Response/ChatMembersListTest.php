<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\ChatMember;
use MaxMessenger\Bot\Model\Response\ChatMembersList;

final class ChatMembersListTest extends Unit
{
    public function testLastPage(): void
    {
        $list = ChatMembersList::newFromData(['members' => []]);

        self::assertSame([], $list->getMembers());
        self::assertNull($list->getMarker());
    }

    public function testMembersAndMarker(): void
    {
        $list = ChatMembersList::newFromData([
            'members' => [
                [
                    'user_id' => 200,
                    'first_name' => 'Иван',
                    'is_bot' => false,
                    'last_access_time' => 1_700_000_000_000,
                    'is_owner' => true,
                    'is_admin' => true,
                    'join_time' => 1_699_000_000_000,
                ],
                [
                    'user_id' => 300,
                    'first_name' => 'Пётр',
                    'is_bot' => false,
                    'last_access_time' => 1_700_000_000_000,
                    'is_owner' => false,
                    'is_admin' => false,
                    'join_time' => 1_699_000_000_000,
                ],
            ],
            'marker' => 300,
        ]);

        $members = $list->getMembers();

        self::assertCount(2, $members);
        self::assertContainsOnlyInstancesOf(ChatMember::class, $members);
        self::assertSame(200, $members[0]->getUserId());
        self::assertSame(300, $members[1]->getUserId());
        self::assertSame($members, $list->getMembers());
        self::assertSame(300, $list->getMarker());
    }
}
