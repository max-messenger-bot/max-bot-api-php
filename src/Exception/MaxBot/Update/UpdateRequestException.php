<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Update;

use MaxMessenger\Bot\Exception\MaxApiException;

/**
 * Update request exception.
 *
 * Base class for exceptions related to update processing.
 */
abstract class UpdateRequestException extends MaxApiException {}
