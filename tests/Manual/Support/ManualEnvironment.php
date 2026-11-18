<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual\Support;

use Throwable;

use function bin2hex;
use function get_debug_type;
use function random_bytes;
use function set_exception_handler;
use function sprintf;

/**
 * Общие настройки ручных сценариев.
 */
final class ManualEnvironment
{
    private static bool $exceptionHandlerInitialized = false;
    /**
     * @var non-empty-string|null
     */
    private static ?string $testRunId = null;

    /**
     * @return non-empty-string Код текущего запуска теста.
     */
    public static function getTestRunId(): string
    {
        return self::$testRunId ??= bin2hex(random_bytes(4));
    }

    /**
     * Устанавливает единый обработчик неперехваченных ошибок ручных тестов.
     */
    public static function initializeExceptionHandler(): void
    {
        if (self::$exceptionHandlerInitialized) {
            return;
        }

        set_exception_handler(static function (Throwable $exception): void {
            Console::console()->error(sprintf(
                '%s: %s (%s:%d)',
                get_debug_type($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
            ));
            exit(1);
        });
        self::$exceptionHandlerInitialized = true;
    }
}
