<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

/**
 * Field cannot be null.
 *
 * Exception thrown when a required field is null.
 */
final class RequiredFieldException extends ValidationException
{
    public function __construct(string $fieldName)
    {
        parent::__construct(sprintf('The "%s" field cannot be null.', $fieldName));
    }
}
