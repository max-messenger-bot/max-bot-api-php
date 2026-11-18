<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotStoppedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogClearedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogMutedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\DialogUnmutedEvent;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Tests\Manual\Support\Console;
use MaxMessenger\Bot\Tests\Manual\Support\ManualEnvironment;
use MaxMessenger\Bot\Tests\Manual\Support\ManualTestReport;
use MaxMessenger\Bot\Tests\Manual\Support\Max;

use function defined;
use function dirname;
use function in_array;
use function mb_strlen;
use function sprintf;

/**
 * Список тестов:
 * - Определение пользователя теста.
 * - Событие отключения уведомлений в диалоге.
 * - Событие включения уведомлений в диалоге.
 * - Событие очистки диалога.
 * - Событие остановки бота.
 * - Событие повторного запуска бота.
 * - Событие удаления диалога.
 * - Событие остановки бота при удалении диалога.
 * - Отправка форматированного сообщения о завершении этапа в личный диалог.
 *
 * Используемые методы API:
 * - getSubscriptions
 * - getMyInfo
 * - getUpdates
 * - sendMessageToUser (тестируется)
 * - sendMessage (тестируется)
 *
 * Используемые события API:
 * - message_created
 * - bot_started (тестируется)
 * - bot_stopped (тестируется)
 * - dialog_muted (тестируется)
 * - dialog_unmuted (тестируется)
 * - dialog_cleared (тестируется)
 * - dialog_removed (тестируется)
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (in_array('--help', $argv, true)) {
    $console = Console::console();
    $console->title('События диалога');
    $console->text('Запуск: php tests/Manual/dialog-events.php');
    $console->note('Нужен пользователь, который выполнит действия в личном диалоге с ботом.');

    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
Max::apiClient();

// Определение пользователя теста по сообщению в личном диалоге.
Max::startDialogTest();
$dialogId = Max::getTestDialogId();
Console::success('Определение пользователя теста прошло успешно.');

// Проверка события отключения уведомлений.
Max::instruction('Отключите уведомления в диалоге с ботом.');
$validator = static fn(DialogMutedEvent $event): bool => $event->getChatId() === $dialogId;
Max::waitForEvent([UpdateType::DialogMuted], $validator);
Console::success('Событие отключения уведомлений получено.');

// Проверка события включения уведомлений.
Max::instruction('Включите уведомления в диалоге с ботом.');
$validator = static fn(DialogUnmutedEvent $event): bool => $event->getChatId() === $dialogId;
Max::waitForEvent([UpdateType::DialogUnmuted], $validator);
Console::success('Событие включения уведомлений получено.');

// Проверка события очистки диалога.
Max::instruction('Очистите историю диалога с ботом.');
$validator = static fn(DialogClearedEvent $event): bool => $event->getChatId() === $dialogId;
Max::waitForEvent([UpdateType::DialogCleared], $validator);
Console::success('Событие очистки диалога получено.');

// Проверка события остановки бота.
Max::instruction('Остановите бота через настройки MAX, потом снова запустите бота кнопкой «Начать».');
$validator = static fn(BotStoppedEvent $event): bool => $event->getChatId() === $dialogId;
Max::waitForEvent([UpdateType::BotStopped], $validator);
Console::success('Событие остановки бота получено.');

// Проверка события повторного запуска бота.
Console::instruction('Снова запустите бота кнопкой «Начать».');
$validator = static fn(BotStartedEvent $event): bool => $event->getChatId() === $dialogId;
Max::waitForEvent([UpdateType::BotStarted], $validator);
Console::success('Событие повторного запуска бота получено.');

// Проверка события удаления диалога.
$bot = Max::getMyInfo();
$username = $bot->getUsername();
$botName = $username !== null ? '@' . $username : $bot->getFullName();
$instructionText = sprintf(
    'Удалите диалог с ботом из списка чатов, потом снова запустите бота %s кнопкой «Начать».',
    $botName,
);
$instruction = new NewMessageBody($instructionText);
if ($username !== null) {
    $instruction->addInlineKeyboard()->addClipboardButton('Скопировать имя бота', $botName);
}
Max::sendMessageToTestUser($instruction);
Console::instruction($instructionText);
$removed = false;
$stopped = false;
$validator = static fn(DialogRemovedEvent|BotStoppedEvent|BotStartedEvent $event): bool
    => $event->getChatId() === $dialogId;
do {
    $event = Max::waitForEvent(
        [UpdateType::DialogRemoved, UpdateType::BotStopped, UpdateType::BotStarted],
        $validator,
    );
    if ($event instanceof DialogRemovedEvent) {
        $removed = true;
        Console::success('Событие удаления диалога получено.');
    } elseif ($event instanceof BotStoppedEvent) {
        $stopped = true;
        Console::success('Событие остановки бота при удалении диалога получено.');
    }
} while (!$event instanceof BotStartedEvent);
Console::success('Событие повторного запуска бота после удаления диалога получено.');
if (!$removed) {
    ManualTestReport::fail('Удаление диалога', 'Событие dialog_removed не получено до bot_started.');
}
if (!$stopped) {
    ManualTestReport::fail('Остановка бота при удалении диалога', 'Событие bot_stopped не получено до bot_started.');
}

$heading = 'Этап 7 завершён';
$text = 'Вернитесь в консоль для просмотра результатов.';
$markup = [['from' => 0, 'length' => mb_strlen($heading), 'level' => 1, 'type' => MarkupElementType::Heading->value]];
Max::sendFormattedTextToTestUser(
    new NewMessageBody("<h1>$heading</h1>\n$text", format: TextFormat::Html),
    "$heading\n$text",
    $markup,
);

if (ManualTestReport::report() && !defined('MaxMessenger\Bot\Tests\Manual\MAX_MANUAL_ALL_INTERACTION')) {
    exit(1);
}
