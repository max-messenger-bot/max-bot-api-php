<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

use function is_string;

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
     * @return non-empty-string Код ошибки (minLength: 1).
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
