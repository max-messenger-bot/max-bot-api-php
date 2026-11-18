<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual\Support;

use MaxMessenger\Bot\Dev\Utils;
use RuntimeException;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_keys;
use function array_map;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function json_encode;
use function rtrim;
use function sort;
use function sprintf;
use function str_starts_with;
use function substr;

/**
 * Общая консоль ручных тестов.
 */
final class Console
{
    private static ?SymfonyStyle $console = null;

    /**
     * Предлагает перейти в тестовый чат для дальнейших инструкций.
     */
    public static function announceChatInstructions(): void
    {
        self::instruction(
            'Перейдите в групповой тестовый чат (ID %d). Дальнейшие инструкции будут там.',
            Max::getTestChatId(),
        );
    }

    /**
     * Предлагает перейти в личный диалог с ботом для дальнейших инструкций.
     */
    public static function announceDialogInstructions(): void
    {
        self::instruction('Перейдите в личный диалог с ботом. Дальнейшие инструкции будут там.');
    }

    /**
     * Сравнивает массивы и выводит diff при несовпадении.
     *
     * @param array<array-key, mixed> $expected Ожидаемые значения.
     * @param array<array-key, mixed> $actual Полученные значения.
     * @param bool $sort Отсортировать значения перед сравнением.
     * @return bool Совпадают ли массивы.
     */
    public static function compareArrays(array $expected, array $actual, bool $sort = false): bool
    {
        if ($sort) {
            sort($expected);
            sort($actual);
        }
        if ($expected !== $actual) {
            self::diff($expected, $actual);

            return false;
        }

        return true;
    }

    /**
     * @return SymfonyStyle Консоль текущего запуска.
     */
    public static function console(): SymfonyStyle
    {
        return self::$console ??= new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
    }

    /**
     * Выводит цветной diff ожидаемых и полученных данных.
     *
     * @param array<array-key, mixed>|string $expected Ожидаемые данные.
     * @param array<array-key, mixed>|string $actual Полученные данные.
     */
    public static function diff(array|string $expected, array|string $actual): void
    {
        $console = self::console();
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR;
        $expected = is_array($expected) ? json_encode($expected, $flags) . "\n" : $expected;
        $actual = is_array($actual) ? json_encode($actual, $flags) . "\n" : $actual;
        $differ = new Differ(new UnifiedDiffOutputBuilder("--- Ожидалось\n+++ Получено\n", true));
        $diff = $differ->diff($expected, $actual);
        foreach (explode("\n", rtrim($diff, "\n")) as $line) {
            $color = match (true) {
                str_starts_with($line, '-') => 'red',
                str_starts_with($line, '+') => 'green',
                str_starts_with($line, '@@') => 'cyan',
                default => 'white',
            };
            $console->writeln('<fg=' . $color . '>' . OutputFormatter::escape($line) . '</>');
        }
    }

    /**
     * Выводит инструкцию для пользователя светло-жёлтым цветом.
     *
     * @param string $message Текст или шаблон инструкции.
     * @param string|int|float ...$arguments Аргументы шаблона.
     */
    public static function instruction(string $message, string|int|float ...$arguments): void
    {
        if ($arguments !== []) {
            $arguments = array_map(
                static fn(string|int|float $argument): string
                    => OutputFormatter::escape((string) $argument),
                $arguments,
            );
            $message = sprintf($message, ...$arguments);
        }
        self::console()->text('<fg=bright-yellow>' . $message . '</>');
    }

    /**
     * Выводит данные в формате JSON.
     *
     * @param mixed $data Данные для вывода.
     */
    public static function json(mixed $data): void
    {
        $json = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        self::console()->writeln(OutputFormatter::escape($json));
    }

    /**
     * Предлагает выбрать токен доступа из файла.
     *
     * @return non-empty-string|null Выбранный токен или null для ручного ввода.
     */
    public static function selectAccessTokenFromFile(): ?string
    {
        $candidates = Utils::projectFileCandidates('.max-tokens');
        [$path, $tokens] = Utils::loadConfigFromFiles($candidates);
        $console = self::console();

        if ($path === null) {
            $root = Utils::projectRoot();
            $example = $root . '/dev/.max-tokens.example';
            $console->note([
                'Файл .max-tokens не найден.',
                'Искал в: ' . implode(', ', $candidates),
                sprintf('Создайте его на основе примера: cp %s %s', $example, $root . '/.max-tokens'),
                'Формат записи: token-name=access-token',
            ]);

            return null;
        }

        if ($tokens === []) {
            $console->warning(
                'Файл .max-tokens пуст или содержит некорректные данные. Формат: token-name=access-token',
            );

            return null;
        }

        $names = array_keys($tokens);
        $choices = [];
        foreach ($names as $index => $name) {
            $choices[$index + 1] = $name;
        }
        $manualChoice = 'Ввести токен вручную';
        while (isset($tokens[$manualChoice])) {
            $manualChoice .= ' (действие)';
        }
        $choices[0] = $manualChoice;
        $selectedName = $console->choice('Выберите токен доступа', $choices, $manualChoice);
        if ($selectedName === $manualChoice) {
            return null;
        }
        if (!is_string($selectedName) || !isset($tokens[$selectedName])) {
            throw new RuntimeException('Выбранный токен не найден.');
        }

        $token = $tokens[$selectedName];
        $console->info(sprintf('Выбран токен: %s (***%s)', $selectedName, substr($token, -4)));

        return $token;
    }

    /**
     * Выводит сообщение об успешном выполнении.
     *
     * @param string $message Текст сообщения.
     */
    public static function success(string $message): void
    {
        self::console()->text('<info>✅ ' . OutputFormatter::escape($message) . '</info>');
    }

    /**
     * Выводит текст в консоль.
     *
     * @param string $message Текст или шаблон сообщения.
     * @param string|int|float ...$arguments Аргументы шаблона.
     */
    public static function text(string $message, string|int|float ...$arguments): void
    {
        if ($arguments !== []) {
            $arguments = array_map(
                static fn(string|int|float $argument): string => OutputFormatter::escape((string) $argument),
                $arguments,
            );
            $message = sprintf($message, ...$arguments);
        }
        self::console()->text($message);
    }

    /**
     * Выводит строку в консоль.
     *
     * @param string $message Текст или шаблон сообщения.
     * @param string|int|float ...$arguments Аргументы шаблона.
     */
    public static function writeln(string $message, string|int|float ...$arguments): void
    {
        if ($arguments !== []) {
            $arguments = array_map(
                static fn(string|int|float $argument): string => OutputFormatter::escape((string) $argument),
                $arguments,
            );
            $message = sprintf($message, ...$arguments);
        }
        self::console()->writeln($message);
    }
}
