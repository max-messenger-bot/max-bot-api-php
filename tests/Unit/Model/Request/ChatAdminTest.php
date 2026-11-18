<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\MaxLengthException;
use MaxMessenger\Bot\Exception\Validation\MinLengthException;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Request\ChatAdmin;

use function json_encode;
use function str_repeat;

use const JSON_UNESCAPED_UNICODE;

final class ChatAdminTest extends Unit
{
    public function testAliasAtMaximumLength(): void
    {
        $alias = str_repeat('я', 16);

        self::assertSame($alias, ChatAdmin::make(200, [], $alias)->getAlias());
    }

    public function testAliasTooLongIsRejected(): void
    {
        $admin = ChatAdmin::make(200, []);

        $this->expectException(MaxLengthException::class);
        $this->expectExceptionMessage(
            'Argument "alias" expects a string of up to 16 characters, but a string of 17 characters was passed.',
        );

        $admin->setAlias(str_repeat('я', 17));
    }

    public function testConstructor(): void
    {
        $admin = new ChatAdmin(200, [ChatAdminPermission::ReadAllMessages], 'Модератор');

        self::assertTrue($admin->issetUserId());
        self::assertTrue($admin->issetPermissions());
        self::assertTrue($admin->issetAlias());
        self::assertSame(200, $admin->getUserId());
        self::assertSame([ChatAdminPermission::ReadAllMessages], $admin->getPermissions());
        self::assertSame('Модератор', $admin->getAlias());
    }

    public function testEmptyAliasIsRejected(): void
    {
        $admin = ChatAdmin::make(200, []);

        $this->expectException(MinLengthException::class);
        $this->expectExceptionMessage('Argument "alias" expects a non-empty string, but an empty string was passed.');

        /** @psalm-suppress InvalidArgument */
        $admin->setAlias('');
    }

    public function testJsonSerialize(): void
    {
        $admin = ChatAdmin::make(
            200,
            [ChatAdminPermission::ReadAllMessages, ChatAdminPermission::PinMessage],
            'Модератор',
        );

        self::assertSame(
            '{"user_id":200,"permissions":["read_all_messages","pin_message"],"alias":"Модератор"}',
            json_encode($admin, JSON_UNESCAPED_UNICODE),
        );
    }

    public function testJsonSerializeWithoutPermissions(): void
    {
        $admin = ChatAdmin::new(200);

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "permissions" field cannot be null.');

        $admin->jsonSerialize();
    }

    public function testJsonSerializeWithoutUserId(): void
    {
        $admin = ChatAdmin::new(permissions: [ChatAdminPermission::Edit]);

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "user_id" field cannot be null.');

        $admin->jsonSerialize();
    }

    public function testNewWithoutArguments(): void
    {
        $admin = ChatAdmin::new();

        self::assertFalse($admin->issetUserId());
        self::assertFalse($admin->issetPermissions());
        self::assertFalse($admin->issetAlias());
        self::assertNull($admin->getAlias());
    }

    public function testSetPermissionsRemovesDuplicatesAndReindexes(): void
    {
        $admin = ChatAdmin::new();

        $result = $admin->setPermissions([
            2 => ChatAdminPermission::Edit,
            5 => ChatAdminPermission::Delete,
            9 => ChatAdminPermission::Edit,
        ]);

        self::assertSame($admin, $result);
        self::assertSame([ChatAdminPermission::Edit, ChatAdminPermission::Delete], $admin->getPermissions());
    }

    public function testSetters(): void
    {
        $admin = ChatAdmin::new();

        self::assertSame($admin, $admin->setUserId(300));
        self::assertSame($admin, $admin->setAlias('Редактор'));
        self::assertSame(300, $admin->getUserId());
        self::assertSame('Редактор', $admin->getAlias());
    }

    public function testUnsetAlias(): void
    {
        $admin = ChatAdmin::make(200, [], 'Модератор');

        $result = $admin->unsetAlias();

        self::assertSame($admin, $result);
        self::assertFalse($admin->issetAlias());
        self::assertSame('{"user_id":200,"permissions":[]}', json_encode($admin));
    }
}
