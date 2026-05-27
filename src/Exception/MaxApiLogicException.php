<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

use LogicException;

/**
 * Logic exception.
 *
 * Base class for logic exceptions in the Max Bot API client.
 */
abstract class MaxApiLogicException extends LogicException
{
}
