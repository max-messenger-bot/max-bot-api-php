<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Прокси-сервер требует авторизации (HTTP 407).
 */
final class ProxyAuthenticationRequiredException extends HttpException {}
