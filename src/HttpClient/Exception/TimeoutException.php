<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception;

use MaxMessenger\Bot\CurlHttpClient\CurlHttpClient;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;

/**
 * Истекло время ожидания: ответ от сервера не пришёл вовремя.
 *
 * {@see CurlHttpClient} бросает её по коду curl `CURLE_OPERATION_TIMEOUTED`: так обозначается
 * и таймаут соединения, и таймаут ответа, и обрыв слишком медленной передачи.
 *
 * {@see PsrHttpClient} распознаёт таймаут по тексту ошибки клиента, потому что PSR-18 не даёт для этого
 * ни кода, ни отдельного типа. У клиентов на curl это работает всегда, у клиентов на потоках PHP таймаут
 * ответа неотличим от прочих сбоев и приходит обычной {@see TemporaryNetworkException}.
 */
final class TimeoutException extends TemporaryNetworkException {}
