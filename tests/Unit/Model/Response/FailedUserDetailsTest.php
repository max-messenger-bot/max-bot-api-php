<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Response;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Model\Response\FailedUserDetails;

final class FailedUserDetailsTest extends Unit
{
    public function testGetters(): void
    {
        $details = FailedUserDetails::newFromData([
            'error_code' => 'add.participant.privacy',
            'user_ids' => [300, 400],
        ]);

        self::assertSame('add.participant.privacy', $details->getErrorCode());
        self::assertSame([300, 400], $details->getUserIds());
    }
}
