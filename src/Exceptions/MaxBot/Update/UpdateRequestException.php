<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\MaxBot\Update;

use MaxMessenger\Bot\Exceptions\RuntimeException;

/**
 * Update request exception.
 *
 * Base class for exceptions related to update processing.
 */
abstract class UpdateRequestException extends RuntimeException
{
}
