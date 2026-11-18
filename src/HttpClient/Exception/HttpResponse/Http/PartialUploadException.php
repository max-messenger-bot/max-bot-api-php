<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http;

/**
 * Медиафайл загружен частично и требует дозагрузки (HTTP 201).
 */
final class PartialUploadException extends HttpException {}
