<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Request\ChatAdmin;
use MaxMessenger\Bot\Model\Request\ChatAdminsList;

use function json_encode;

final class ChatAdminsListTest extends Unit
{
    public function testAddAdmin(): void
    {
        $first = ChatAdmin::make(200, [ChatAdminPermission::Edit]);
        $second = ChatAdmin::make(300, [ChatAdminPermission::Delete]);
        $list = ChatAdminsList::new();

        $result = $list->addAdmin($first);
        $list->addAdmin($second);

        self::assertSame($list, $result);
        self::assertTrue($list->issetAdmins());
        self::assertSame([$first, $second], $list->getAdmins());
    }

    public function testConstructor(): void
    {
        $admin = ChatAdmin::make(200, [ChatAdminPermission::Edit]);

        $list = new ChatAdminsList([$admin]);

        self::assertTrue($list->issetAdmins());
        self::assertSame([$admin], $list->getAdmins());
    }

    public function testJsonSerialize(): void
    {
        $list = ChatAdminsList::make([ChatAdmin::make(200, [ChatAdminPermission::Edit])]);

        self::assertSame('{"admins":[{"user_id":200,"permissions":["edit"]}]}', json_encode($list));
    }

    public function testJsonSerializeWithoutAdmins(): void
    {
        $list = ChatAdminsList::new();

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "admins" field cannot be null.');

        $list->jsonSerialize();
    }

    public function testNewWithoutAdmins(): void
    {
        self::assertFalse(ChatAdminsList::new()->issetAdmins());
    }

    public function testSetAdminsReindexesList(): void
    {
        $first = ChatAdmin::make(200, [ChatAdminPermission::Edit]);
        $second = ChatAdmin::make(300, [ChatAdminPermission::Delete]);
        $list = ChatAdminsList::new();

        $result = $list->setAdmins([4 => $first, 8 => $second]);

        self::assertSame($list, $result);
        self::assertSame([$first, $second], $list->getAdmins());
    }
}
