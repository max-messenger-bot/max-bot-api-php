<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use MaxMessenger\Bot\Exception\MaxApiException;

/**
 * Validation exception.
 *
 * Base class for validation exceptions in the Max Bot API client.
 */
abstract class ValidationException extends MaxApiException {}
