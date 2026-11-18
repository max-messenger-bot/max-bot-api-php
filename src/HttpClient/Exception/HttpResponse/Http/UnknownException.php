<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Ошибка с HTTP-кодом, для которого нет отдельного класса исключения.
 */
final class UnknownException extends HttpException {}
