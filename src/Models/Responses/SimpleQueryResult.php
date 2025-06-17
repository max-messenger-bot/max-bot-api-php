<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Простой ответ на запрос.
 */
class SimpleQueryResult extends BaseResponseModel
{
    /**
     * @var array{
     *     success: bool,
     *     message?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Объяснительное сообщение, если результат не был успешным.
     */
    public function getMessage(): ?string
    {
        return $this->data['message'] ?? null;
    }

    /**
     * @return bool `true`, если запрос был успешным, `false` — в противном случае.
     */
    public function isSuccess(): bool
    {
        return ($this->data['success'] ?? null) === true;
    }
}
