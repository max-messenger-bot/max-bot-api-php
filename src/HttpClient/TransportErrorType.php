<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient;

/**
 * Результат классификации ошибки HTTP-транспорта.
 */
enum TransportErrorType
{
    case Permanent;
    case TemporaryNetwork;
    case Timeout;
}
