<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

use MaxMessenger\Bot\Model\Response\SimpleQueryResult;

/**
 * Простая ошибка API.
 *
 * Возникает, если ответ API не подтверждает успех операции: поле `success` не равно `true`.
 */
class SimpleQueryError extends MaxApiException
{
    /**
     * @param SimpleQueryResult $result Исходный результат запроса API.
     */
    public function __construct(
        private readonly SimpleQueryResult $result,
    ) {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        parent::__construct($result->getMessage() ?: 'The server did not return the message.');
    }

    /**
     * @return string|null Код ошибки API или `null`, если сервер его не передал.
     */
    public function getErrorCode(): ?string
    {
        return $this->result->getCode();
    }

    /**
     * @return SimpleQueryResult Исходный результат запроса API.
     */
    public function getResult(): SimpleQueryResult
    {
        return $this->result;
    }
}
