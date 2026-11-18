<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\PhotoToken;
use MaxMessenger\Bot\Model\Response\PhotoTokens;

use function array_keys;

final class PhotoTokensTest extends Unit
{
    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidData(): iterable
    {
        yield 'нет photos' => [[]];
        yield 'photos не массив' => [['photos' => 'token']];
        yield 'пустой photos' => [['photos' => []]];
    }

    public function testGetPhotos(): void
    {
        $tokens = PhotoTokens::newFromData([
            'photos' => [
                'first' => ['token' => 'token-1'],
                'second' => ['token' => 'token-2'],
            ],
        ]);

        $photos = $tokens->getPhotos();

        self::assertSame(['first', 'second'], array_keys($photos));
        self::assertInstanceOf(PhotoToken::class, $photos['first']);
        self::assertSame('token-1', $photos['first']->getToken());
        self::assertSame('token-2', $photos['second']->getToken());
        self::assertSame($photos, $tokens->getPhotos());
    }

    public function testIsValid(): void
    {
        self::assertTrue(PhotoTokens::newFromData(['photos' => ['first' => ['token' => 'token-1']]])->isValid());
    }

    /**
     * @param array<string, mixed> $data
     * @dataProvider invalidData
     */
    public function testIsValidWithInvalidData(array $data): void
    {
        self::assertFalse(PhotoTokens::newFromData($data)->isValid());
    }
}
