<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use function is_string;
use function str_contains;

/**
 * Сервер возвращает это, если возникло исключение при вашем запросе.
 */
class Error extends BaseResponseModel
{
    /**
     * @var array{
     *     error?: string,
     *     code: non-empty-string,
     *     message: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return non-empty-string Код ошибки.
     */
    public function getCode(): string
    {
        return $this->data['code'];
    }

    /**
     * @return string|null Ошибка.
     */
    public function getError(): ?string
    {
        return $this->data['error'] ?? null;
    }

    /**
     * @return string Читаемое описание ошибки.
     */
    public function getMessage(): string
    {
        return $this->data['message'];
    }

    /**
     * Проверяет, что ошибка связана с неготовностью вложения.
     *
     * Возвращает `true`, если файл ещё не обработан сервером и отправку стоит повторить.
     *
     * @return bool `true`, если вложение ещё не готово.
     */
    public function isAttachmentNotReady(): bool
    {
        return $this->getCode() === 'attachment.not.ready'
            || str_contains($this->getMessage(), 'attachment.file.not.processed');
    }

    /**
     * Checks that the data passed to the model is correct for the given model.
     */
    public function isValid(): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return isset($this->data['code'], $this->data['message'])
            && is_string($this->data['code'])
            && is_string($this->data['message']);
    }
}
