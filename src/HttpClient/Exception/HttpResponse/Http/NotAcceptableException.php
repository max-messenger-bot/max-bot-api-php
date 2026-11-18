<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Сервер не может вернуть ответ в запрошенном формате (HTTP 406).
 */
final class NotAcceptableException extends HttpException {}
