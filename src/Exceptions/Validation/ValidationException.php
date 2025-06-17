<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use MaxMessenger\Bot\Exceptions\RuntimeException;

/**
 * Validation exception.
 *
 * Base class for validation exceptions in the Max Bot API client.
 */
abstract class ValidationException extends RuntimeException
{
}
