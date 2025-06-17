<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Uploaders;

use MaxMessenger\Bot\Exceptions\RuntimeException;

/**
 * Base class for uploader exceptions.
 *
 * Abstract exception for errors that occur during the file upload process.
 */
abstract class UploaderException extends RuntimeException
{
}
