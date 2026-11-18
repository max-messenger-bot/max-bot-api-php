<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

const MAX_MANUAL_ALL_INTERACTION = true;

use MaxMessenger\Bot\Tests\Manual\Support\Console;
use MaxMessenger\Bot\Tests\Manual\Support\ManualEnvironment;
use MaxMessenger\Bot\Tests\Manual\Support\ManualTestReport;
use MaxMessenger\Bot\Tests\Manual\Support\Max;

use function array_keys;
use function count;
use function dirname;
use function filter_var;
use function in_array;
use function sprintf;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$console = Console::console();

if (in_array('--help', $argv, true)) {
    $console->title('Полный интерактивный тест диалога, чата и канала');
    $console->text('Запуск: php tests/Manual/all-interaction.php');
    $console->note([
        'После запуска выберите стартовый блок; затем выполнятся все последующие.',
        'Нужны тестовый канал, новый тестовый чат и два пользователя.',
    ]);
    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
Max::apiClient();

$blocks = [
    'bot-interaction.php' => 'Личный диалог, кнопки и команды',
    'chat-setup-interaction.php' => 'Групповой чат: создание и права чтения',
    'chat-messages-interaction.php' => 'Групповой чат: свойства, сообщения и действия',
    'chat-members-interaction.php' => 'Групповой чат: участники и права',
    'chat-finish-interaction.php' => 'Групповой чат: завершение',
    'channel-interaction.php' => 'Канал и комментарии',
    'dialog-events.php' => 'События диалога',
];
$filesByNumber = [];
foreach (array_keys($blocks) as $file) {
    $number = count($filesByNumber) + 1;
    $filesByNumber[$number] = $file;
}
$console->newLine(2);
$console->text('Выберите блок, с которого начать; после него выполнятся все оставшиеся:');
foreach ($filesByNumber as $number => $file) {
    $console->text(sprintf('  %2d. [%-30s] %s', $number, $file, $blocks[$file]));
}
$console->newLine();
do {
    $answer = (string) $console->ask(sprintf('Номер блока [1–%d, Enter=1]', count($blocks)), '1');
    $selectedNumber = filter_var($answer, FILTER_VALIDATE_INT);
    if ($selectedNumber === false || !isset($filesByNumber[$selectedNumber])) {
        $console->warning('Введите номер блока из списка.');
    }
} while ($selectedNumber === false || !isset($filesByNumber[$selectedNumber]));
$selectedFile = $filesByNumber[$selectedNumber];

$started = false;
foreach ($blocks as $file => $title) {
    $started = $started || $file === $selectedFile;
    if (!$started) {
        continue;
    }
    $console->section($title);
    ManualTestReport::reset();

    /** @psalm-suppress UnresolvableInclude */
    require __DIR__ . '/' . $file;
}

if (ManualTestReport::globalReport()) {
    exit(1);
}
