<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\CurlHttpClient\Exception;

use MaxMessenger\Bot\HttpClient\Exception\HttpClientException;

/**
 * Ошибка curl, не связанная с сетью.
 *
 * Код ошибки совпадает с кодом curl.
 */
final class CurlException extends HttpClientException {}
