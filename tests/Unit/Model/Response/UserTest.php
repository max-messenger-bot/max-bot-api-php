<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\User;

final class UserTest extends Unit
{
    public function testFullNameWithEmptyLastName(): void
    {
        $user = User::newFromData(['user_id' => 200, 'first_name' => 'Иван', 'last_name' => '', 'is_bot' => false]);

        self::assertSame('Иван', $user->getFullName());
    }

    public function testFullNameWithLastName(): void
    {
        $user = User::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'is_bot' => false,
        ]);

        self::assertSame('Иван Петров', $user->getFullName());
        self::assertSame('<a href="max://user/200">Иван Петров</a>', $user->getHtmlLink());
        self::assertSame('[Иван Петров](max://user/200)', $user->getMarkdownLink());
    }

    public function testFullUser(): void
    {
        $user = User::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'is_bot' => false,
            'last_activity_time' => 1_700_000_000_000,
        ]);

        self::assertSame(200, $user->getUserId());
        self::assertSame('Иван', $user->getFirstName());
        self::assertSame('Петров', $user->getLastName());
        self::assertNull($user->getUsername());
        self::assertFalse($user->isBot());
        self::assertSame(1_700_000_000_000, $user->getLastActivityTimeRaw());
        self::assertSame('2023-11-14 22:13:20', $user->getLastActivityTime()?->format('Y-m-d H:i:s'));
    }

    public function testLinksEscapeSpecialCharacters(): void
    {
        $user = User::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван [SDK] *dev* & <admin> \\',
            'is_bot' => false,
        ]);

        self::assertSame(
            '<a href="max://user/200">Иван [SDK] *dev* &amp; &lt;admin&gt; \\</a>',
            $user->getHtmlLink(),
        );
        self::assertSame(
            '[Иван \\[SDK\\] \\*dev\\* & \\<admin\\> \\\\](max://user/200)',
            $user->getMarkdownLink(),
        );
    }

    public function testMinimalBot(): void
    {
        $user = User::newFromData(['user_id' => 300, 'first_name' => 'Бот', 'is_bot' => true]);

        self::assertTrue($user->isBot());
        self::assertNull($user->getLastName());
        self::assertNull($user->getUsername());
        self::assertNull($user->getLastActivityTime());
        self::assertNull($user->getLastActivityTimeRaw());
        self::assertSame('Бот', $user->getFullName());
        self::assertSame('<a href="max://user/300">Бот</a>', $user->getHtmlLink());
        self::assertSame('[Бот](max://user/300)', $user->getMarkdownLink());
    }

    public function testUsernameForBot(): void
    {
        $user = User::newFromData([
            'user_id' => 300,
            'first_name' => 'Бот',
            'username' => 'test_bot',
            'is_bot' => true,
        ]);

        self::assertSame('test_bot', $user->getUsername());
    }
}
