<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Превышено количество запросов.
 */
final class TooManyRequestsException extends MaxHttpException {}
