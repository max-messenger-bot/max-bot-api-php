<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Простой ответ на запрос.
 *
 * @link https://dev.max.ru/docs-api/
 */
class SimpleQueryResult extends BaseResponseModel
{
    /**
     * @var array{
     *     success: bool,
     *     code?: string,
     *     message?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return string|null Код ошибки API или `null`, если сервер его не передал.
     */
    public function getCode(): ?string
    {
        return $this->data['code'] ?? null;
    }

    /**
     * @return string|null Сообщение об ошибке.
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
