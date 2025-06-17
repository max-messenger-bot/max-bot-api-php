<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions\Validation;

use function sprintf;

/**
 * At least one field must be non-null.
 *
 * Exception thrown when at least one field from a list must be provided.
 */
final class RequiredOneFieldException extends ValidationException
{
    /**
     * @param string[] $fieldNames
     */
    public function __construct(array $fieldNames)
    {
        $fields = implode('", "', $fieldNames);

        parent::__construct(sprintf('At least one field of "%s" cannot be null.', $fields));
    }
}
