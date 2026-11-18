<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Сервер не поддерживает запрошенную возможность (HTTP 501).
 */
final class NotImplementedException extends HttpException {}
