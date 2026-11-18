<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Model\Response\UserWithPhoto;

final class UserWithPhotoTest extends Unit
{
    public function testGetters(): void
    {
        $user = UserWithPhoto::newFromData([
            'user_id' => 200,
            'first_name' => 'Иван',
            'is_bot' => false,
            'description' => 'О себе',
            'avatar_url' => 'https://example.test/avatar.jpg',
            'full_avatar_url' => 'https://example.test/avatar-full.jpg',
        ]);

        self::assertInstanceOf(User::class, $user);
        self::assertSame(200, $user->getUserId());
        self::assertSame('О себе', $user->getDescription());
        self::assertSame('https://example.test/avatar.jpg', $user->getAvatarUrl());
        self::assertSame('https://example.test/avatar-full.jpg', $user->getFullAvatarUrl());
    }

    public function testWithoutPhoto(): void
    {
        $user = UserWithPhoto::newFromData(['user_id' => 200, 'first_name' => 'Иван', 'is_bot' => false]);

        self::assertNull($user->getDescription());
        self::assertNull($user->getAvatarUrl());
        self::assertNull($user->getFullAvatarUrl());
    }
}
