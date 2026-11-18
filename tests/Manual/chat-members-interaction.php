<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\HttpException;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\UserRemovedFromChatEvent;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\ChatAdmin;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Tests\Manual\Support\ChatPermissions;
use MaxMessenger\Bot\Tests\Manual\Support\Console;
use MaxMessenger\Bot\Tests\Manual\Support\ManualEnvironment;
use MaxMessenger\Bot\Tests\Manual\Support\ManualTestReport;
use MaxMessenger\Bot\Tests\Manual\Support\Max;

use function defined;
use function dirname;
use function implode;
use function in_array;
use function mb_strlen;

/**
 * Список тестов:
 * - Определение пользователя теста при отдельном запуске.
 * - Определение тестового группового чата.
 * - Проверка прав бота в чате.
 * - Событие добавления второго пользователя в чат.
 * - Событие удаления второго пользователя из чата.
 * - Добавление второго пользователя ботом.
 * - Назначение второго пользователя администратором.
 * - Снятие прав администратора со второго пользователя.
 * - Удаление второго пользователя ботом.
 * - Отображение действий с участником в чате.
 * - Запрет удаления ботом чата, созданного пользователем.
 * - Отправка форматированного сообщения о завершении этапа.
 *
 * Используемые методы API:
 * - getSubscriptions
 * - getMyInfo
 * - getUpdates
 * - sendMessageToUser (тестируется)
 * - sendMessage (тестируется)
 * - sendMessageToChat (тестируется)
 * - answerOnCallback
 * - getMembership (тестируется)
 * - addMembers (тестируется)
 * - getMembers (тестируется)
 * - getAdmins (тестируется)
 * - postAdmins (тестируется)
 * - deleteAdmin (тестируется)
 * - deleteAdmins (тестируется)
 * - removeMember (тестируется)
 * - deleteChat (тестируется)
 *
 * Используемые события API:
 * - bot_added
 * - message_created
 * - message_callback
 * - bot_admin_permissions_changed
 * - user_added (тестируется)
 * - user_removed (тестируется)
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (in_array('--help', $argv, true)) {
    $console = Console::console();
    $console->title('Групповой чат: участники и права');
    $console->text('Запуск: php tests/Manual/chat-members-interaction.php');
    $console->note('Нужны тестовый чат, его владелец, бот и второй тестовый пользователь.');

    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
$client = Max::apiClient();

// Определение тестового группового чата.
Max::startChatTest();
$botId = Max::getMyInfo()->getUserId();
$userId = Max::getTestUserId();
$chatId = Max::getTestChatId();
Console::success('Определение тестового группового чата прошло успешно.');

// Проверка прав бота в чате.
$requiredPermissions = [ChatAdminPermission::AddRemoveMembers, ChatAdminPermission::AddAdmins];
$permissionValidator = static fn(BotAdminPermissionsChangedEvent $event): bool
    => $event->getChatId() === $chatId && $event->getBotId() === $botId;
do {
    $membership = $client->getMembership($chatId);
    $missingPermissions = [];
    foreach ($requiredPermissions as $permission) {
        if (!$membership->hasPermission($permission)) {
            $missingPermissions[] = ChatPermissions::label($permission);
        }
    }
    if ($missingPermissions !== []) {
        $instructionText = 'Добавьте боту права: ' . implode(', ', $missingPermissions) . '.';
        Max::instructionToChat('%s', $instructionText);
        Max::waitForEvent([UpdateType::BotAdminPermissionsChanged], $permissionValidator, userId: null);
    }
} while ($missingPermissions !== []);
Console::success('Права бота в чате проверены успешно.');

// Проверка события добавления второго пользователя в чат.
Max::instructionToChat('Добавьте в этот чат второго тестового пользователя.');
$addedValidator = static fn(UserAddedToChatEvent $event): bool
    => $event->getChatId() === $chatId && $event->getUserId() !== $botId && $event->getUserId() !== $userId;
$added = Max::waitForEvent([UpdateType::UserAdded], $addedValidator, userId: null);
$secondUserId = $added->getUserId();
Console::text('Добавлен пользователь %s (ID %d).', $added->getUser()->getFullName(), $secondUserId);
Console::success('Событие добавления второго пользователя в чат получено.');

// Проверка события удаления второго пользователя из чата.
Max::instructionToChat('Удалите из этого чата второго тестового пользователя (ID %d).', $secondUserId);
$removedValidator = static fn(UserRemovedFromChatEvent $event): bool
    => $event->getChatId() === $chatId && $event->getUserId() === $secondUserId;
Max::waitForEvent([UpdateType::UserRemoved], $removedValidator, userId: null);
Console::success('Событие удаления второго пользователя из чата получено.');
Console::announceChatInstructions();

// Проверка добавления второго пользователя ботом.
$client->addMembers($chatId, [$secondUserId]);
$member = $client->getMembers($chatId, [$secondUserId])->getMembers()[0] ?? null;
if ($member?->getUserId() === $secondUserId) {
    Console::success('Добавление пользователя ботом проверено успешно.');
} else {
    ManualTestReport::fail('Добавление пользователя ботом', 'Пользователь не найден среди участников чата.');
}

// Проверка назначения второго пользователя администратором.
$client->postAdmins($chatId, [ChatAdmin::make($secondUserId, [ChatAdminPermission::AddRemoveMembers])]);
$member = $client->getMembers($chatId, [$secondUserId])->getMembers()[0] ?? null;
$foundAdmin = false;
foreach ($client->getAdmins($chatId)->getMembers() as $admin) {
    $foundAdmin = $foundAdmin || $admin->getUserId() === $secondUserId;
}
$expectedAdmin = ['администратор' => true, 'право управления участниками' => true, 'в списке' => true];
$actualAdmin = [
    'администратор' => $member?->isAdmin() ?? false,
    'право управления участниками' => $member?->hasPermission(ChatAdminPermission::AddRemoveMembers) ?? false,
    'в списке' => $foundAdmin,
];
if (!Console::compareArrays($expectedAdmin, $actualAdmin)) {
    ManualTestReport::fail('Назначение администратора', 'Данные пользователя не совпали с ожидаемыми.');
} elseif (Max::askInTestChat('Второй пользователь появился в списке администраторов чата?')) {
    Console::success('Назначение второго пользователя администратором проверено успешно.');
} else {
    ManualTestReport::fail('Назначение администратора', 'Пользователь отметил проверку как непройденную.');
}

// Проверка снятия прав администратора со второго пользователя.
$client->deleteAdmin($chatId, $secondUserId);
$member = $client->getMembers($chatId, [$secondUserId])->getMembers()[0] ?? null;
$foundAdmin = false;
foreach ($client->getAdmins($chatId)->getMembers() as $admin) {
    $foundAdmin = $foundAdmin || $admin->getUserId() === $secondUserId;
}
$expectedAdmin = ['участник' => true, 'администратор' => false, 'в списке' => false];
$actualAdmin = [
    'участник' => $member?->getUserId() === $secondUserId,
    'администратор' => $member?->isAdmin(),
    'в списке' => $foundAdmin,
];
if (!Console::compareArrays($expectedAdmin, $actualAdmin)) {
    ManualTestReport::fail('Снятие прав администратора', 'Данные пользователя не совпали с ожидаемыми.');
} elseif (Max::askInTestChat('Второй пользователь исчез из списка администраторов чата?')) {
    Console::success('Снятие прав администратора проверено успешно.');
} else {
    ManualTestReport::fail('Снятие прав администратора', 'Пользователь отметил проверку как непройденную.');
}

// Проверка удаления второго пользователя ботом.
$client->removeMember($chatId, $secondUserId);
$members = $client->getMembers($chatId, [$secondUserId])->getMembers();
if ($members !== []) {
    ManualTestReport::fail('Удаление пользователя ботом', 'Пользователь остался в списке участников чата.');
} else {
    Console::success('Удаление пользователя ботом проверено успешно.');
}

// Проверка отображения действий с участником в чате.
$question = 'Бот добавил второго пользователя, назначил администратором, снял права и удалил из чата. Всё видно?';
if (Max::askInTestChat($question)) {
    Console::success('Отображение действий с участником проверено успешно.');
} else {
    ManualTestReport::fail('Отображение действий с участником', 'Пользователь отметил проверку как непройденную.');
}

// Проверка, что бот не может удалить чат, созданный пользователем.
$chatDeleted = false;
try {
    // Намеренно проверяем сохранившееся поведение устаревшего метода API.
    /** @psalm-suppress DeprecatedMethod */
    $client->deleteChat($chatId);
    $chatDeleted = true;
    ManualTestReport::fail('Удаление чата ботом', 'Бот удалил чат, созданный пользователем.');
} catch (SimpleQueryError $exception) {
    $expectedError = [
        'success' => false,
        'message' => 'Insufficient access rights to perform this action',
        'code' => 'chat.denied',
    ];
    $actualError = $exception->getResult()->getRawData();
    if (!Console::compareArrays($expectedError, $actualError)) {
        ManualTestReport::fail('Удаление чата ботом', 'Raw-данные ошибки не совпали с ожидаемыми.');
    } else {
        Console::success('Удаление созданного пользователем чата ботом отклонено.');
    }
} catch (HttpException $exception) {
    Console::writeln('%s', $exception->getResponse()->getBody());
    ManualTestReport::fail(
        'Удаление чата ботом',
        'Неожиданный ответ HTTP ' . $exception->getResponse()->getHttpCode() . '.',
    );
}

$heading = 'Этап 4 завершён';
$text = 'Вернитесь в консоль для просмотра результатов.';
$markup = [['from' => 0, 'length' => mb_strlen($heading), 'level' => 1, 'type' => MarkupElementType::Heading->value]];
$completionMessage = new NewMessageBody("<h1>$heading</h1>\n$text", format: TextFormat::Html);
if ($chatDeleted) {
    Max::sendFormattedTextToTestUser($completionMessage, "$heading\n$text", $markup);
} else {
    Max::sendFormattedTextToTestChat($completionMessage, "$heading\n$text", $markup);
}

if (ManualTestReport::report() && !defined('MaxMessenger\Bot\Tests\Manual\MAX_MANUAL_ALL_INTERACTION')) {
    exit(1);
}
