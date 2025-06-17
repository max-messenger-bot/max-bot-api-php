<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse;

use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http\MaxHttpException;

/**
 * Upload error.
 *
 * Exception thrown when a file upload fails.
 */
final class UploadException extends MaxHttpException
{
}
