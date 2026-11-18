<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\ModifyMembersResult;
use MaxMessenger\Bot\Model\Response\SimpleQueryResult;

final class ModifyMembersResultTest extends Unit
{
    public function testFailedMembers(): void
    {
        $result = ModifyMembersResult::newFromData([
            'success' => false,
            'message' => 'Не удалось добавить участников',
            'failed_user_ids' => [300, 400],
            'failed_user_details' => [
                ['error_code' => 'add.participant.privacy', 'user_ids' => [300]],
                ['error_code' => 'add.participant.not.found', 'user_ids' => [400]],
            ],
        ]);

        self::assertInstanceOf(SimpleQueryResult::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertSame('Не удалось добавить участников', $result->getMessage());
        self::assertSame([300, 400], $result->getFailedUserIds());

        $details = $result->getFailedUserDetails();
        self::assertNotNull($details);
        self::assertCount(2, $details);
        self::assertSame('add.participant.privacy', $details[0]->getErrorCode());
        self::assertSame([400], $details[1]->getUserIds());
        self::assertSame($details, $result->getFailedUserDetails());
    }

    public function testSuccess(): void
    {
        $result = ModifyMembersResult::newFromData(['success' => true]);

        self::assertTrue($result->isSuccess());
        self::assertNull($result->getFailedUserIds());
        self::assertNull($result->getFailedUserDetails());
        self::assertNull($result->getFailedUserDetails());
    }
}
