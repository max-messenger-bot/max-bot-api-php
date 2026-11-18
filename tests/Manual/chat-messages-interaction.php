<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\HttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageRemovedEvent;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Enum\UpdateType;
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
 * - Получение списка участников чата.
 * - Получение списка администраторов чата.
 * - Получение сообщений по ID.
 * - Получение сообщений из чата.
 * - Отображение статуса набора текста.
 * - Отображение статуса отправки фотографии.
 * - Отображение статуса отправки видео.
 * - Отображение статуса записи аудио.
 * - Отображение статуса отправки файла.
 * - Отметка прочтения сообщения.
 * - Закрепление сообщения.
 * - Открепление сообщения.
 * - Редактирование сообщения бота.
 * - Удаление сообщения бота.
 * - Удаление сообщения пользователя.
 * - Ответ на сообщение пользователя в чате.
 * - Пересылка сообщения пользователя в личный диалог.
 * - Пересылка сообщения пользователя в чат.
 * - Ответ на сообщение пользователя в личном диалоге.
 * - Событие редактирования сообщения пользователя.
 * - Событие удаления сообщения пользователя.
 * - Отправка форматированного сообщения о завершении этапа в чат.
 *
 * Используемые методы API:
 * - getSubscriptions
 * - getMyInfo
 * - getUpdates
 * - sendMessageToUser
 * - sendMessage (тестируется)
 * - sendMessageToChat (тестируется)
 * - answerOnCallback
 * - getMembership (тестируется)
 * - getMembers (тестируется)
 * - getAdmins (тестируется)
 * - getMessagesById (тестируется)
 * - getMessagesFromChat (тестируется)
 * - getMessages (тестируется)
 * - sendAction (тестируется)
 * - pinMessage (тестируется)
 * - getPinnedMessage (тестируется)
 * - unpinMessage (тестируется)
 * - editMessage (тестируется)
 * - getMessageById (тестируется)
 * - deleteMessage (тестируется)
 *
 * Используемые события API:
 * - bot_added
 * - bot_admin_permissions_changed
 * - message_created
 * - message_callback
 * - message_edited (тестируется)
 * - message_removed (тестируется)
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (in_array('--help', $argv, true)) {
    $console = Console::console();
    $console->title('Групповой чат: свойства, сообщения и действия');
    $console->text('Запуск: php tests/Manual/chat-messages-interaction.php');
    $console->note('Нужен тестовый чат с ботом и пользователь с правами владельца.');

    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
$client = Max::apiClient();

// Определение тестового группового чата.
Max::startChatTest();
$runId = ManualEnvironment::getTestRunId();
$botId = Max::getMyInfo()->getUserId();
$userId = Max::getTestUserId();
$chatId = Max::getTestChatId();
Console::success('Определение тестового группового чата прошло успешно.');

// Проверка прав бота в чате.
$requiredPermissions = [
    ChatAdminPermission::Write,
    ChatAdminPermission::PinMessage,
    ChatAdminPermission::AddRemoveMembers,
    ChatAdminPermission::AddAdmins,
];
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

// Проверка получения списков участников и администраторов чата.
$members = $client->getMembers($chatId)->getMembers();
$admins = $client->getAdmins($chatId)->getMembers();
if ($members === [] || $admins === []) {
    ManualTestReport::fail('Получение списков участников и администраторов', 'Один или оба списка пусты.');
} else {
    Console::success('Получение списков участников и администраторов прошло успешно.');
}

// Проверка получения списка сообщений по ID и из чата.
$messageText = 'Проверка списка сообщений SDK ' . $runId;
$sentMessage = Max::sendMessageToTestChat(new NewMessageBody($messageText));
$messageId = $sentMessage->getBody()->getMid();
$messageLists = [
    'по ID' => $client->getMessagesById([$messageId])->getMessages(),
    'из чата' => $client->getMessagesFromChat($chatId)->getMessages(),
];
foreach ($messageLists as $source => $messages) {
    $found = false;
    foreach ($messages as $message) {
        if ($message->getBody()->getMid() === $messageId) {
            $found = true;

            break;
        }
    }
    if ($found) {
        Console::success('Получение сообщений ' . $source . ' прошло успешно.');
    } else {
        ManualTestReport::fail('Получение сообщений ' . $source, 'Отправленное сообщение не найдено в списке.');
    }
}

// Проверка статусов бота и отметки прочтения сообщения.
$actions = [
    SenderAction::TypingOn->value => 'бот печатает',
    SenderAction::SendingPhoto->value => 'бот отправляет фото',
    SenderAction::SendingVideo->value => 'бот отправляет видео',
    SenderAction::SendingAudio->value => 'бот записывает аудио',
    SenderAction::SendingFile->value => 'бот отправляет файл',
    SenderAction::MarkSeen->value => 'сообщение прочитано ботом',
];
foreach (SenderAction::cases() as $action) {
    $label = $actions[$action->value];
    if ($action === SenderAction::MarkSeen) {
        $seenText = 'Проверка прочтения SDK ' . $runId;
        $seenInstruction = new NewMessageBody('Нажмите кнопку, чтобы отправить сообщение для проверки прочтения.');
        $seenInstruction->addInlineKeyboard()->addMessageButton($seenText);
        Max::sendMessageToTestChat($seenInstruction);
        $seenValidator = static fn(MessageCreatedEvent $event): bool
            => $event->getMessage()->getRecipient()->getChatId() === $chatId;
        Max::waitForMessageCreatedEvent($seenValidator, ChatType::Chat);
    }
    $question = 'Проверьте действие «' . $label . '». Нажмите «Показать», затем подтвердите результат.';
    $showAction = static fn(): null => $client->sendAction($chatId, $action);
    if (Max::askInTestChatWithAction($question, 'Показать', $showAction)) {
        Console::success('Действие «' . $label . '» проверено успешно.');
    } else {
        ManualTestReport::fail('Действие «' . $label . '»', 'Пользователь отметил проверку как непройденную.');
    }
}

// Проверка закрепления и открепления сообщения в чате.
$pinMessage = Max::sendMessageToTestChat(new NewMessageBody('Сообщение для проверки закрепления SDK ' . $runId));
$pinMessageId = $pinMessage->getBody()->getMid();
$client->pinMessage($chatId, $pinMessageId);
$pinnedId = $client->getPinnedMessage($chatId)->getMessage()?->getBody()->getMid();
if ($pinnedId !== $pinMessageId) {
    Console::diff(['mid' => $pinMessageId], ['mid' => $pinnedId]);
    ManualTestReport::fail('Закрепление сообщения', 'Закреплённое сообщение не совпало с ожидаемым.');
} elseif (Max::askInTestChat('Сообщение для проверки закреплено в чате?')) {
    Console::success('Закрепление сообщения проверено успешно.');
} else {
    ManualTestReport::fail('Закрепление сообщения', 'Пользователь отметил проверку как непройденную.');
}
$client->unpinMessage($chatId);
$pinnedId = $client->getPinnedMessage($chatId)->getMessage()?->getBody()->getMid();
if ($pinnedId !== null) {
    Console::diff(['mid' => null], ['mid' => $pinnedId]);
    ManualTestReport::fail('Открепление сообщения', 'В чате осталось закреплённое сообщение.');
} elseif (Max::askInTestChat('Сообщение для проверки больше не закреплено в чате?')) {
    Console::success('Открепление сообщения проверено успешно.');
} else {
    ManualTestReport::fail('Открепление сообщения', 'Пользователь отметил проверку как непройденную.');
}

// Проверка редактирования сообщения бота.
$botMessage = Max::sendMessageToTestChat(new NewMessageBody('Сообщение бота SDK ' . $runId));
$editInstruction = new NewMessageBody('Нажмите «Редактировать», чтобы изменить тестовое сообщение бота.');
$editInstruction->addInlineKeyboard()->addCallbackButton('Редактировать', 'manual:action');
$editMessage = Max::sendMessageToTestChat($editInstruction);
$editEvent = Max::waitForMessageCallbackEvent($editMessage->getBody()->getMid());
$editEvent->answer('Изменяю тестовое сообщение бота.');
$editedText = 'Изменённое сообщение бота SDK ' . $runId;
$client->editMessage($botMessage->getBody()->getMid(), new NewMessageBody($editedText));
$actualText = $client->getMessageById($botMessage->getBody()->getMid())->getText();
$editPassed = $actualText === $editedText;
if (!$editPassed) {
    Console::diff($editedText, $actualText);
    ManualTestReport::fail('Редактирование сообщения бота', 'Текст сообщения не совпал с ожидаемым.');
}
if ($editPassed) {
    Console::success('Редактирование сообщения бота проверено успешно.');
}

// Проверка удаления сообщений бота и пользователя.
$userMessageText = 'Сообщение пользователя SDK ' . $runId;
$userInstruction = new NewMessageBody('Нажмите кнопку, чтобы отправить тестовое сообщение в чат.');
$userInstruction->addInlineKeyboard()->addMessageButton($userMessageText);
Max::sendMessageToTestChat($userInstruction);
$userMessageValidator = static fn(MessageCreatedEvent $event): bool
    => $event->getMessage()->getRecipient()->getChatId() === $chatId;
$userMessage = Max::waitForMessageCreatedEvent($userMessageValidator, ChatType::Chat)->getMessage();
$deleteInstruction = new NewMessageBody('Нажмите «Удалить», чтобы удалить оба тестовых сообщения.');
$deleteInstruction->addInlineKeyboard()->addCallbackButton('Удалить', 'manual:action');
$deleteMessage = Max::sendMessageToTestChat($deleteInstruction);
$deleteEvent = Max::waitForMessageCallbackEvent($deleteMessage->getBody()->getMid());
$deleteEvent->answer('Удаляю тестовые сообщения.');
$deletePassed = true;
$messageIds = ['бота' => $botMessage->getBody()->getMid(), 'пользователя' => $userMessage->getBody()->getMid()];
foreach ($messageIds as $owner => $id) {
    try {
        $client->deleteMessage($id);
    } catch (HttpException $exception) {
        Console::writeln('%s', $exception->getResponse()->getBody());
        $errorMessage = 'Ошибка HTTP ' . $exception->getResponse()->getHttpCode() . '.';
        ManualTestReport::fail('Удаление сообщения ' . $owner, $errorMessage);
        $deletePassed = false;

        continue;
    }
    try {
        $client->getMessageById($id);
        ManualTestReport::fail('Удаление сообщения ' . $owner, 'Сообщение доступно после удаления.');
        $deletePassed = false;
    } catch (NotFoundException) {
    }
}
if ($deletePassed) {
    Console::success('Удаление сообщений бота и пользователя проверено успешно.');
}

// Проверка ответов и пересылки сообщения пользователя.
$replyText = 'Сообщение для ответов SDK ' . $runId;
$replyInstruction = new NewMessageBody('Нажмите кнопку, чтобы отправить сообщение для проверки ответов и пересылки.');
$replyInstruction->addInlineKeyboard()->addMessageButton($replyText);
Max::sendMessageToTestChat($replyInstruction);
$replyValidator = static fn(MessageCreatedEvent $event): bool
    => $event->getMessage()->getRecipient()->getChatId() === $chatId;
$messageEvent = Max::waitForMessageCreatedEvent($replyValidator, ChatType::Chat);
$replyMessageId = $messageEvent->getMessage()->getBody()->getMid();
$reply = $messageEvent->reply('Ответ в чате SDK ' . $runId, asReply: true)->getMessage();
$forwardToUser = $messageEvent->forwardToUser($userId)->getMessage();
$forwardToChat = $messageEvent->forwardToChat($chatId)->getMessage();
$dialogReply = $messageEvent->replyToUser('Личный ответ SDK ' . $runId, forwardOrigMessage: true)->getMessage();
$expectedLinks = [
    'ответ в чате' => ['type' => MessageLinkType::Reply->value, 'mid' => $replyMessageId],
    'пересылка в диалог' => ['type' => MessageLinkType::Forward->value, 'mid' => $replyMessageId],
    'пересылка в чат' => ['type' => MessageLinkType::Forward->value, 'mid' => $replyMessageId],
];
$actualLinks = [];
$messages = ['ответ в чате' => $reply, 'пересылка в диалог' => $forwardToUser, 'пересылка в чат' => $forwardToChat];
foreach ($messages as $name => $message) {
    $link = $message->getLink();
    $actualLinks[$name] = ['type' => $link?->getType()?->value, 'mid' => $link?->getMessage()->getMid()];
}
$linksMatch = Console::compareArrays($expectedLinks, $actualLinks);
if (!$linksMatch) {
    ManualTestReport::fail('Ответы и пересылка', 'Связи сообщений не совпали с ожидаемыми.');
}
if ($dialogReply->getText() !== 'Личный ответ SDK ' . $runId) {
    Console::diff('Личный ответ SDK ' . $runId, $dialogReply->getText());
    ManualTestReport::fail('Ответ в личный диалог', 'Текст ответа не совпал с ожидаемым.');
}
if (!Max::askInTestChat('В личном диалоге видны ответ и пересланные сообщения из чата?')) {
    ManualTestReport::fail('Ответы и пересылка', 'Пользователь отметил проверку как непройденную.');
} elseif ($linksMatch && $dialogReply->getText() === 'Личный ответ SDK ' . $runId) {
    Console::success('Ответы и пересылка сообщения проверены успешно.');
}

// Проверка событий редактирования и удаления сообщения пользователя.
Max::sendMessageToTestChat(new NewMessageBody('Отправьте любое сообщение в этот чат.'));
$userMessageValidator = static fn(MessageCreatedEvent $event): bool
    => $event->getMessage()->getRecipient()->getChatId() === $chatId;
$userMessage = Max::waitForMessageCreatedEvent($userMessageValidator, ChatType::Chat)->getMessage();
$userMessageId = $userMessage->getBody()->getMid();
Max::sendMessageToTestChat(new NewMessageBody('Отредактируйте своё последнее сообщение.'));
$editedValidator = static fn(MessageEditedEvent $event): bool
    => $event->getMessage()->getBody()->getMid() === $userMessageId;
Max::waitForEvent([UpdateType::MessageEdited], $editedValidator);
Console::success('Событие редактирования сообщения пользователя получено.');
$removeUserInstruction = new NewMessageBody('Удалите своё изменённое сообщение.');
Max::sendMessageToTestChat($removeUserInstruction);
$removedValidator = static fn(MessageRemovedEvent $event): bool
    => $event->getChatId() === $chatId && $event->getMessageId() === $userMessageId;
Max::waitForEvent([UpdateType::MessageRemoved], $removedValidator);
Console::success('Событие удаления сообщения пользователя получено.');

$heading = 'Этап 3 завершён';
$text = 'Вернитесь в консоль для просмотра результатов.';
$markup = [['from' => 0, 'length' => mb_strlen($heading), 'level' => 1, 'type' => MarkupElementType::Heading->value]];
Max::sendFormattedTextToTestChat(
    new NewMessageBody("<h1>$heading</h1>\n$text", format: TextFormat::Html),
    "$heading\n$text",
    $markup,
);

if (ManualTestReport::report() && !defined('MaxMessenger\Bot\Tests\Manual\MAX_MANUAL_ALL_INTERACTION')) {
    exit(1);
}
