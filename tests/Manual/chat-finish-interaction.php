<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\HttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\UserRemovedFromChatEvent;
use MaxMessenger\Bot\Model\Enum\ChatStatus;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Tests\Manual\Support\Console;
use MaxMessenger\Bot\Tests\Manual\Support\ManualEnvironment;
use MaxMessenger\Bot\Tests\Manual\Support\ManualTestReport;
use MaxMessenger\Bot\Tests\Manual\Support\Max;
use RuntimeException;

use function defined;
use function dirname;
use function in_array;
use function mb_strlen;

/**
 * Список тестов:
 * - Определение пользователя теста.
 * - Определение тестового группового чата.
 * - Событие выхода владельца из чата после удаления чата.
 * - Получение и вывод информации о чате после удаления.
 * - Проверка статуса удалённого чата.
 * - Отправка форматированного сообщения о завершении этапа в личный диалог.
 *
 * Используемые методы API:
 * - getSubscriptions
 * - getMyInfo
 * - getUpdates
 * - getMembership
 * - getChat (тестируется)
 * - sendMessageToUser (тестируется)
 * - sendMessageToChat
 * - sendMessage (тестируется)
 *
 * Используемые события API:
 * - bot_added
 * - message_created
 * - bot_admin_permissions_changed
 * - user_removed (тестируется)
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (in_array('--help', $argv, true)) {
    $console = Console::console();
    $console->title('Групповой чат: завершение');
    $console->text('Запуск: php tests/Manual/chat-finish-interaction.php');
    $console->note('Нужен тестовый чат с ботом и пользователь, который сможет удалить чат.');

    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
$client = Max::apiClient();

// Определение тестового группового чата.
Max::startChatTest();
$chatId = Max::getTestChatId();
$ownerId = $client->getChat($chatId)->getOwnerId()
    ?? throw new RuntimeException('Не удалось определить владельца тестового чата.');
Console::success('Определение тестового группового чата прошло успешно.');

// Удаление тестового чата пользователем.
Max::instructionToChat(
    'Удалите этот тестовый чат через клиент MAX, пока бот ещё состоит в нём в роли администратора. '
    . 'После удаления вернитесь в консоль.',
);
// Проверка события выхода владельца из чата.
Console::instruction(
    'Если событие удаления чата не получено, отправьте боту любое сообщение в личном диалоге. '
    . 'Проверка события будет отмечена как непройденная.',
);
$validator = static function (BaseEvent $event) use ($chatId, $ownerId): bool {
    if ($event instanceof UserRemovedFromChatEvent) {
        return !$event->isChannel() && $event->getChatId() === $chatId && $event->getUserId() === $ownerId;
    }
    if (!$event instanceof MessageCreatedEvent) {
        return false;
    }
    $message = $event->getMessage();

    return $message->getRecipient()->getChatType() === ChatType::Dialog
        && $message->getSender()?->getUserId() === Max::getTestUserId();
};
$event = Max::waitForEvent([UpdateType::UserRemoved, UpdateType::MessageCreated], $validator, userId: null);
if ($event instanceof UserRemovedFromChatEvent) {
    Console::success('Событие выхода владельца из чата получено.');
} else {
    ManualTestReport::fail('Событие выхода владельца из чата', 'Пользователь сообщил, что событие не получено.');
}

// Получение информации о чате после удаления.
try {
    $chat = $client->getChat($chatId);
    Console::text('Информация о чате после удаления:');
    Console::json($chat->getRawData());
    if ($chat->getStatus() !== ChatStatus::Closed) {
        ManualTestReport::fail(
            'Статус удалённого чата',
            'Ожидался статус closed, получен: ' . $chat->getStatusRaw() . '.',
        );
    } else {
        Console::success('Статус удалённого чата: closed.');
    }
} catch (ForbiddenException|NotFoundException $exception) {
    ManualTestReport::fail(
        'Получение удалённого чата',
        'Чат недоступен: HTTP ' . $exception->getResponse()->getHttpCode() . '.',
    );
} catch (HttpException $exception) {
    ManualTestReport::fail(
        'Получение удалённого чата',
        'Неожиданный ответ HTTP ' . $exception->getResponse()->getHttpCode() . '.',
    );
}

$heading = 'Этап 5 завершён';
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
