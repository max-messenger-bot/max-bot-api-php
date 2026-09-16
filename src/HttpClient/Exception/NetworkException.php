<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception;

/**
 * Ошибка сети: ответ от сервера не получен.
 *
 * Соединение не установлено, оборвалось или истекло время ожидания. Код ошибки совпадает с кодом curl;
 * распространённые значения перечислены константами класса. У клиентов на основе PSR-18 кода нет —
 * там доступно только описание ошибки.
 */
final class NetworkException extends HttpClientException
{
    public const UNSUPPORTED_PROTOCOL = 1;
    public const FAILED_INIT = 2;
    public const URL_MALFORMAT = 3;
    public const COULDNT_RESOLVE_HOST = 6;
    public const COULDNT_CONNECT = 7;
    public const WRITE_ERROR = 24;
    public const READ_ERROR = 26;
    public const OUT_OF_MEMORY = 27;
    public const OPERATION_TIMEOUTED = 28;
    public const HTTP_RANGE_ERROR = 33;
    public const SSL_CONNECT_ERROR = 35;
    public const ABORTED_BY_CALLBACK = 42;
    public const HTTP_PORT_FAILED = 45;
    public const TOO_MANY_REDIRECTS = 47;
    public const GOT_NOTHING = 52;
    public const SEND_ERROR = 55;
    public const RECV_ERROR = 56;
    public const SSL_CERTPROBLEM = 58;
    public const SSL_CIPHER = 59;
    public const SSL_PEER_CERTIFICATE = 60;
    public const BAD_CONTENT_ENCODING = 61;
    public const FILESIZE_EXCEEDED = 63;
}
