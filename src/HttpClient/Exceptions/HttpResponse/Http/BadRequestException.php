<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http;

/**
 * Неверный запрос.
 */
final class BadRequestException extends MaxHttpException
{
    /**
     * Проверяет, что ошибка связана с неготовностью вложения.
     *
     * Возвращает `true`, если файл ещё не обработан сервером.
     */
    public function isAttachmentNotReady(): bool
    {
        return $this->error->getCode() === 'attachment.not.ready' ||
            str_contains($this->error->getMessage(), 'attachment.file.not.processed');
    }
}
