<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Unit\MaxBot;

use Codeception\Test\Unit;
use MaxMessenger\Bot\Exception\MaxBot\Update\BadRequestException;
use MaxMessenger\Bot\MaxBot;

/**
 * Проверка типа содержимого Webhook-запросов.
 */
final class ContentTypeValidationTest extends Unit
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function acceptedContentTypes(): iterable
    {
        yield 'JSON' => ['application/json'];
        yield 'JSON с кодировкой' => ['application/json; charset=UTF-8'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function rejectedContentTypes(): iterable
    {
        yield 'нет заголовка' => [''];
        yield 'текст' => ['text/plain'];
        yield 'XML' => ['application/xml'];
    }

    /**
     * @dataProvider acceptedContentTypes
     */
    public function testValidateRequestHeadersAcceptsJson(string $contentType): void
    {
        $this->expectNotToPerformAssertions();

        (new MaxBot('test-token'))->validateRequestHeaders('POST', $contentType, '2', '');
    }

    /**
     * @dataProvider rejectedContentTypes
     */
    public function testValidateRequestHeadersRejectsOtherContentTypes(string $contentType): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Content-Type must contain application/json.');

        (new MaxBot('test-token'))->validateRequestHeaders('POST', $contentType, '2', '');
    }
}
