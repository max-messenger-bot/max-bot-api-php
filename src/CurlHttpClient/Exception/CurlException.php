<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\CurlHttpClient\Exception;

use MaxMessenger\Bot\HttpClient\Exception\HttpClientException;

/**
 * Ошибка curl: код и текст, как их отдал curl.
 *
 * Код ошибки совпадает с кодом curl (константы `CURLE_*`).
 */
final class CurlException extends HttpClientException {}
