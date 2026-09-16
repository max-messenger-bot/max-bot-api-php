<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;

/**
 * Внутренняя ошибка сервера.
 */
final class InternalHttpException extends HttpException implements TemporaryExceptionInterface {}
