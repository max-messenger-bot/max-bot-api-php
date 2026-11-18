<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use RuntimeException;

/**
 * Исключение со строковым кодом, как у `PDOException`.
 */
final class FakeStringCodeException extends RuntimeException
{
    public function __construct(string $message, string $code)
    {
        parent::__construct($message);

        // Воспроизводим строковый код PDOException, допустимый в PHP, но не в модели типов Psalm.
        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->code = $code;
    }
}
