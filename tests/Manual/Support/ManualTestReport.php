<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual\Support;

use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Throwable;

use function count;
use function htmlspecialchars;
use function sprintf;

/**
 * Собирает ошибки ручных сценариев и печатает итоговый отчёт.
 */
final class ManualTestReport
{
    /**
     * @var list<string>
     */
    private static array $allFailures = [];
    /**
     * @var list<string>
     */
    private static array $failures = [];

    /**
     * Выводит непройденную проверку и добавляет её в общий отчёт.
     *
     * @param non-empty-string $name Название проверки.
     * @param bool $notifyUser Отправить пользователю теста уведомление о проблеме в диалог.
     */
    public static function fail(string $name, string|Throwable $reason, bool $notifyUser = false): void
    {
        $message = $reason instanceof Throwable
            ? sprintf('%s (%s:%d)', $reason->getMessage(), $reason->getFile(), $reason->getLine())
            : $reason;
        $failure = $name . ': ' . $message;
        self::$failures[] = $failure;
        self::$allFailures[] = $failure;
        $console = Console::console();
        $console->error(OutputFormatter::escape($failure));
        if ($notifyUser) {
            try {
                Max::apiClient()->sendMessageToUser(
                    Max::getTestUserId(),
                    new NewMessageBody(
                        '<mark>🔴 Обнаружена проблема в тесте: '
                        . htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                        . '. Подробнее в консоли.</mark>',
                        format: TextFormat::Html,
                    ),
                );
            } catch (Throwable $exception) {
                $console->error(OutputFormatter::escape(
                    'Не удалось отправить уведомление в чат: ' . $exception->getMessage(),
                ));
            }
        }
    }

    /**
     * Печатает общий отчёт по всем тестам запуска.
     *
     * @return bool true, если есть ошибки.
     */
    public static function globalReport(): bool
    {
        return self::printReport(self::$allFailures, 'Все проверки ручных тестов прошли успешно.');
    }

    /**
     * Печатает отчёт по текущему отдельному тесту.
     *
     * @return bool true, если есть ошибки.
     */
    public static function report(): bool
    {
        return self::printReport(self::$failures, 'Все проверки текущего этапа прошли успешно.');
    }

    /**
     * Очищает ошибки перед началом отдельного теста.
     */
    public static function reset(): void
    {
        self::$failures = [];
    }

    /**
     * @param list<string> $failures
     */
    private static function printReport(array $failures, string $successMessage): bool
    {
        $console = Console::console();
        if ($failures === []) {
            $console->success($successMessage);

            return false;
        }

        $console->error([
            sprintf('Непройденные проверки: %d', count($failures)),
            ...$failures,
        ]);

        return true;
    }
}
