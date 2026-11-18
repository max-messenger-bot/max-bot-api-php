<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\Model\Request;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\PhotoAttachmentRequestPayload;
use MaxMessenger\Bot\Model\Request\ValidateRequiredTrait;

use function json_encode;

use const JSON_UNESCAPED_SLASHES;

/**
 * Проверки трейта {@see ValidateRequiredTrait} через публичные методы моделей, которые его используют.
 */
final class ValidateRequiredTraitTest extends Unit
{
    public function testOneOfFieldsAcceptsAnyFilledField(): void
    {
        PhotoAttachmentRequestPayload::new('https://example.test/photo.png')->validateRequired();
        PhotoAttachmentRequestPayload::new(token: 'photo-token')->validateRequired();

        self::assertSame(
            '{"token":"photo-token"}',
            json_encode(PhotoAttachmentRequestPayload::new(token: 'photo-token'), JSON_UNESCAPED_SLASHES),
        );
    }

    public function testOneOfFieldsRejectsEmptyStrings(): void
    {
        PhotoAttachmentRequestPayload::$disableValidation = true;
        /** @psalm-suppress InvalidArgument */
        $payload = PhotoAttachmentRequestPayload::new('', '');

        $this->expectException(RequiredOneFieldException::class);

        $payload->validateRequired();
    }

    public function testRequiredFieldRejectsEmptyStringEvenWithValidationDisabled(): void
    {
        BotCommand::$disableValidation = true;
        /** @psalm-suppress InvalidArgument */
        $command = BotCommand::new('');

        $this->expectException(RequiredFieldException::class);
        $this->expectExceptionMessage('The "name" field cannot be null.');

        $command->jsonSerialize();
    }

    protected function tearDown(): void
    {
        BotCommand::$disableValidation = false;
        PhotoAttachmentRequestPayload::$disableValidation = false;

        parent::tearDown();
    }
}
