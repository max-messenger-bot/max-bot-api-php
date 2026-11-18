<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Сервер не может обработать запрос (HTTP 422).
 */
final class UnprocessableEntityException extends HttpException {}
