<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Сервер не может вернуть ответ в запрошенном формате.
 */
final class NotAcceptableException extends HttpException {}
