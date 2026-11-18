<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

use LogicException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\HttpException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\BotRemovedFromChatEvent;
use MaxMessenger\Bot\MaxBot\Event\ChatTitleChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\ChatStatus;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Tests\Manual\Support\ChatPermissions;
use MaxMessenger\Bot\Tests\Manual\Support\Console;
use MaxMessenger\Bot\Tests\Manual\Support\ManualEnvironment;
use MaxMessenger\Bot\Tests\Manual\Support\ManualTestReport;
use MaxMessenger\Bot\Tests\Manual\Support\Max;

use function array_filter;
use function array_map;
use function array_values;
use function defined;
use function dirname;
use function implode;
use function in_array;
use function mb_strlen;
use function sprintf;

/**
 * Список тестов:
 * - Определение тестового чата по событию добавления бота.
 * - Определение пользователя по событию добавления бота.
 * - Событие добавления бота в чат.
 * - Событие удаления бота из чата.
 * - Отказ в получении чата после удаления бота.
 * - Отказ в отправке сообщения после удаления бота.
 * - Отправка сообщения после повторного добавления бота.
 * - Выход бота из чата.
 * - Повторное добавление бота после выхода.
 * - Отсутствие сообщений пользователей у бота без права чтения.
 * - Назначение бота администратором без прав чтения и редактирования.
 * - Отказ в изменении названия чата без права редактирования.
 * - Отсутствие сообщений пользователей у администратора без права чтения.
 * - Выдача боту права редактирования чата.
 * - Выдача боту права чтения сообщений.
 * - Изменение названия чата.
 * - Получение изменённого названия чата.
 * - Событие изменения названия чата.
 * - Получение сообщения пользователя с правом чтения.
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
 * - getChat (тестируется)
 * - getMembership (тестируется)
 * - leaveChat (тестируется)
 * - editChat (тестируется)
 * - editMessage (тестируется)
 *
 * Используемые события API:
 * - bot_added (тестируется)
 * - bot_removed (тестируется)
 * - bot_admin_permissions_changed (тестируется)
 * - chat_title_changed (тестируется)
 * - message_created (тестируется)
 * - message_callback
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (in_array('--help', $argv, true)) {
    $console = Console::console();
    $console->title('Групповой чат: создание и права чтения');
    $console->text('Запуск: php tests/Manual/chat-setup-interaction.php');
    $console->note('Нужны токен бота и пользователь, который создаст чат и добавит бота.');

    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
$client = Max::apiClient();

// Проверка события добавления бота в чат.
$chatTitle = 'SDK ' . ManualEnvironment::getTestRunId();
$instructionTemplate = 'Создайте групповой чат «%s» и добавьте в него бота. Затем вернитесь в консоль.';
if (Max::hasTestUser()) {
    $instruction = new NewMessageBody(sprintf($instructionTemplate, $chatTitle));
    $instruction->addInlineKeyboard()->addClipboardButton('Скопировать название', $chatTitle);
    Max::sendMessageToTestUser($instruction);
}
Console::instruction($instructionTemplate, $chatTitle);
$validator = static function (BotAddedToChatEvent $event) use ($client, $chatTitle): bool {
    return !$event->isChannel() && $client->getChat($event->getChatId())->getTitle() === $chatTitle;
};
$added = Max::waitForEvent([UpdateType::BotAdded], $validator, userId: null);
Max::setTestUser($added->getUser());
Max::setTestChatId($added->getChatId());
Console::text('Создан чат «%s» (ID %d).', $chatTitle, $added->getChatId());
Console::success('Событие добавления бота в чат получено.');

// Проверка события удаления бота из чата.
$chatId = $added->getChatId();
Max::instructionToChat('Удалите бота из группового чата «%s», затем снова добавьте его в этот чат.', $chatTitle);
$removeValidator = static function (BotRemovedFromChatEvent $event) use ($chatId): bool {
    return !$event->isChannel() && $event->getChatId() === $chatId;
};
Max::waitForEvent([UpdateType::BotRemoved], $removeValidator);
Console::success('Событие удаления бота из чата получено.');

// Проверка получения чата после удаления бота.
try {
    $client->getChat($chatId);
    ManualTestReport::fail('Получение чата после удаления бота', 'Удалённый бот смог получить чат.', true);
} catch (NotFoundException $exception) {
    $expectedError = ['code' => 'error.not.found', 'message' => 'error.not.found'];
    $actualError = $exception->getError()?->getRawData();
    if (!Console::compareArrays($expectedError, $actualError ?? [])) {
        ManualTestReport::fail('Получение чата после удаления бота', 'Некорректный ответ HTTP 404.', true);
    } else {
        Console::success('Получение чата после удаления бота отклонено: HTTP 404.');
    }
} catch (HttpException $exception) {
    $response = $exception->getResponse();
    Console::text('%s', $response->getBody());
    ManualTestReport::fail(
        'Получение чата после удаления бота',
        'Неожиданный ответ HTTP ' . $response->getHttpCode() . '.',
        true,
    );
}

// Проверка отправки сообщения после удаления бота из чата.
try {
    $client->sendMessageToChat(
        $chatId,
        new NewMessageBody('Сообщение удалённого бота SDK ' . ManualEnvironment::getTestRunId()),
    );
    ManualTestReport::fail('Отправка сообщения после удаления бота', 'Удалённый бот смог отправить сообщение.', true);
} catch (NotFoundException $exception) {
    $expectedError = ['code' => 'error.not.found', 'message' => 'error.not.found'];
    $actualError = $exception->getError()?->getRawData();
    if (!Console::compareArrays($expectedError, $actualError ?? [])) {
        ManualTestReport::fail('Отправка сообщения после удаления бота', 'Некорректный ответ HTTP 404.', true);
    } else {
        Console::success('Отправка сообщения после удаления бота отклонена: HTTP 404.');
    }
} catch (HttpException $exception) {
    $response = $exception->getResponse();
    Console::writeln('%s', $response->getBody());
    ManualTestReport::fail(
        'Отправка сообщения после удаления бота',
        'Неожиданный ответ HTTP ' . $response->getHttpCode() . '.',
        true,
    );
}

// Проверка отправки сообщения после повторного добавления бота.
Console::instruction('Добавьте бота снова в групповой чат «%s».', $chatTitle);
$addValidator = static function (BotAddedToChatEvent $event) use ($chatId): bool {
    return !$event->isChannel()
        && $event->getChatId() === $chatId;
};
Max::waitForEvent([UpdateType::BotAdded], $addValidator);
$messageText = 'Сообщение после повторного добавления бота SDK ' . ManualEnvironment::getTestRunId();
Max::sendMessageToTestChat(new NewMessageBody($messageText));
if (Max::isSentMessageValid()) {
    Console::success('Отправка сообщения после повторного добавления бота прошла успешно.');
}

// Проверка выхода бота из чата и повторного добавления.
Max::instructionToChat(
    'Сейчас бот покинет чат, вам нужно добавить его ещё раз как обычного пользователя (не администратора).',
);
$client->leaveChat($chatId);
try {
    $status = $client->getChat($chatId)->getStatus();
    if ($status !== ChatStatus::Left) {
        ManualTestReport::fail('Выход бота из чата', 'После выхода статус чата не стал left.');
    } else {
        Console::success('Выход бота из чата проверен успешно.');
    }
} catch (NotFoundException $exception) {
    $expectedError = ['code' => 'error.not.found', 'message' => 'error.not.found'];
    $actualError = $exception->getError()?->getRawData();
    if (!Console::compareArrays($expectedError, $actualError ?? [])) {
        ManualTestReport::fail('Выход бота из чата', 'Некорректный ответ HTTP 404.');
    } else {
        Console::success('Выход бота из чата проверен успешно: чат недоступен.');
    }
}
Max::waitForEvent([UpdateType::BotAdded], $addValidator, userId: null);
Console::success('Повторное добавление бота в чат после выхода проверено успешно.');
Console::announceChatInstructions();

// Проверка, что бот не получает сообщения пользователей без права чтения.
if ($client->getMembership($chatId)->isAdmin()) {
    $adminInstruction = new NewMessageBody('Уберите бота из администраторов чата и нажмите «Продолжить».');
    $adminInstruction->addInlineKeyboard()->addCallbackButton('Продолжить', 'manual:action');
    $adminMessage = Max::sendMessageToTestChat($adminInstruction);
    do {
        $adminEvent = Max::waitForMessageCallbackEvent($adminMessage->getBody()->getMid());
        $isAdmin = $client->getMembership($chatId)->isAdmin();
        $adminEvent->answer($isAdmin ? $adminInstruction : new NewMessageBody('Права администратора сняты.'));
    } while ($isAdmin);
}
$checkUnreadableMessages = static function (string $scenario) use ($chatId): void {
    $buttonText = 'Проверка чтения ' . $scenario . ' SDK ' . ManualEnvironment::getTestRunId();
    $instruction = new NewMessageBody('Сначала нажмите кнопку отправки сообщения, затем кнопку «Проверка».');
    $instruction
        ->addInlineKeyboard()
        ->addMessageButton($buttonText)
        ->newRow()
        ->addCallbackButton('Проверка', 'manual:action');
    $instructionMessage = Max::sendMessageToTestChat($instruction);
    $instructionId = $instructionMessage->getBody()->getMid();
    $validator = static function (BaseEvent $event) use ($chatId, $buttonText, $instructionId): bool {
        if ($event instanceof MessageCreatedEvent) {
            return $event->getMessage()->getRecipient()->getChatId() === $chatId
                && $event->getMessage()->getText() === $buttonText;
        }

        return $event instanceof MessageCallbackEvent
            && $event->getMessage()->getBody()->getMid() === $instructionId;
    };
    $messageReceived = false;
    do {
        $event = Max::waitForEvent([UpdateType::MessageCreated, UpdateType::MessageCallback], $validator);
        if ($event instanceof MessageCreatedEvent) {
            $messageReceived = true;
        }
    } while (!$event instanceof MessageCallbackEvent);
    if ($messageReceived) {
        $event->answer('Бот получил сообщение пользователя. Подробнее в консоли.');
        ManualTestReport::fail('Чтение сообщений без права (' . $scenario . ')', 'Бот получил сообщение пользователя.');
    } else {
        $event->answer('Бот не получил сообщение пользователя. Проверка пройдена.');
        Console::success('Бот не получил сообщение пользователя без права чтения (' . $scenario . ').');
    }
};
$checkUnreadableMessages('участник');

// Проверка назначения бота администратором без прав чтения сообщений и редактирования чата.
$withoutPermissionsText = sprintf(
    'Назначьте бота администратором без прав «%s» и «%s».',
    ChatPermissions::label(ChatAdminPermission::ReadAllMessages),
    ChatPermissions::label(ChatAdminPermission::ChangeChatInfo),
);
Max::sendMessageToTestChat(new NewMessageBody($withoutPermissionsText));
$adminValidator = static fn(BotAdminPermissionsChangedEvent $event): bool
    => $event->getChatId() === $chatId && $event->getBotId() === Max::getMyInfo()->getUserId();
do {
    Max::waitForEvent([UpdateType::BotAdminPermissionsChanged], $adminValidator, userId: null);
    $membership = $client->getMembership($chatId);
    $permissions = $membership->getPermissionsRaw();
    $unwantedPermissions = array_values(array_filter(
        $permissions ?? [],
        static fn(string $permission): bool => in_array($permission, [
            ChatAdminPermission::ReadAllMessages->value,
            ChatAdminPermission::ChangeChatInfo->value,
        ], true),
    ));
    $adminWithoutPermissions = $membership->isAdmin() && $permissions !== null && $unwantedPermissions === [];
    if (!$adminWithoutPermissions) {
        $text = $unwantedPermissions === []
            ? $withoutPermissionsText
            : 'У бота включены права: ' . implode(', ', array_map(ChatPermissions::label(...), $unwantedPermissions))
            . '. Уберите эти права бота.';
        Max::sendMessageToTestChat(new NewMessageBody($text));
    }
} while (!$adminWithoutPermissions);
Console::success('Назначение бота администратором без прав чтения и редактирования проверено успешно.');

// Проверка отказа в изменении названия чата без права редактирования.
try {
    $client->editChat($chatId, new ChatPatch(title: $chatTitle . ' test'));
    ManualTestReport::fail('Изменение названия чата без права', 'Боту удалось изменить название чата.');
    $client->editChat($chatId, new ChatPatch(title: $chatTitle));
} catch (ForbiddenException $exception) {
    $expectedError = [
        'code' => 'chat.denied',
        'message' => 'Chat owner has restricted changing the chat name by participants',
    ];
    $actualError = $exception->getError()?->getRawData();
    if (!Console::compareArrays($expectedError, $actualError ?? [])) {
        ManualTestReport::fail('Изменение названия чата без права', 'Некорректный ответ HTTP 403.');
    } else {
        Console::success('Изменение названия чата без права отклонено: HTTP 403.');
    }
} catch (HttpException $exception) {
    Console::writeln('%s', $exception->getResponse()->getBody());
    ManualTestReport::fail(
        'Изменение названия чата без права',
        'Неожиданный ответ HTTP ' . $exception->getResponse()->getHttpCode() . '.',
    );
}

// Проверка, что администратор без прав не получает сообщения пользователей.
$checkUnreadableMessages('администратор');

// Проверка права редактирования чата и события изменения названия.
$editInstruction = new NewMessageBody(
    sprintf(
        'Включите боту права «%s» и «%s».',
        ChatPermissions::label(ChatAdminPermission::ChangeChatInfo),
        ChatPermissions::label(ChatAdminPermission::ReadAllMessages),
    ),
);
Max::sendMessageToTestChat($editInstruction);
do {
    Max::waitForEvent([UpdateType::BotAdminPermissionsChanged], $adminValidator, userId: null);
    $membership = $client->getMembership($chatId);
    $canEdit = $membership->isAdmin()
        && $membership->hasPermission(ChatAdminPermission::ChangeChatInfo)
        && $membership->hasPermission(ChatAdminPermission::ReadAllMessages);
    if (!$canEdit) {
        $missingPermissions = [];
        foreach ([ChatAdminPermission::ChangeChatInfo, ChatAdminPermission::ReadAllMessages] as $permission) {
            if (!$membership->hasPermission($permission)) {
                $missingPermissions[] = ChatPermissions::label($permission);
            }
        }
        $text = $missingPermissions === []
            ? 'Назначьте бота администратором чата.'
            : 'Включите недостающие права: ' . implode(', ', $missingPermissions) . '.';
        Max::sendMessageToTestChat(new NewMessageBody($text));
    }
} while (!$canEdit);
Max::sendMessageToTestChat(new NewMessageBody('Права редактирования чата и чтения сообщений включены.'));
$originalTitle = $client->getChat($chatId)->getTitle()
    ?? throw new LogicException('Не удалось получить название чата.');
$editedTitle = 'SDK edited ' . ManualEnvironment::getTestRunId();
$editResult = $client->editChat($chatId, new ChatPatch(title: $editedTitle));
if ($editResult->getTitle() !== $editedTitle) {
    Console::diff($editedTitle, $editResult->getTitle() ?? '');
    ManualTestReport::fail('Ответ на изменение названия чата', 'Название в ответе не совпало с ожидаемым.');
} else {
    Console::success('Название в ответе на изменение чата проверено успешно.');
}
$storedTitle = $client->getChat($chatId)->getTitle();
if ($storedTitle !== $editedTitle) {
    Console::diff($editedTitle, $storedTitle ?? '');
    ManualTestReport::fail('Получение изменённого названия чата', 'Название чата не совпало с ожидаемым.');
} else {
    Console::success('Получение изменённого названия чата проверено успешно.');
}
if ($storedTitle === null) {
    throw new LogicException('Не удалось получить название чата.');
}
$titleInstruction = new NewMessageBody(
    'Верните чату название «' . $originalTitle . '» и нажмите «Я поменял», если бот не ответил.',
);
$titleInstruction
    ->addInlineKeyboard()
    ->addClipboardButton('Скопировать', $originalTitle)
    ->newRow()
    ->addCallbackButton('Я поменял', 'manual:action');
$titleMessage = Max::sendMessageToTestChat($titleInstruction);
$titleMessageId = $titleMessage->getBody()->getMid();
$titleValidator = static function (BaseEvent $event) use ($chatId, $titleMessageId): bool {
    if ($event instanceof ChatTitleChangedEvent) {
        return $event->getChatId() === $chatId;
    }

    return $event instanceof MessageCallbackEvent
        && $event->getMessage()->getBody()->getMid() === $titleMessageId;
};
do {
    /** @var ChatTitleChangedEvent|MessageCallbackEvent $titleEvent */
    $titleEvent = Max::waitForEvent([UpdateType::ChatTitleChanged, UpdateType::MessageCallback], $titleValidator);
    if ($titleEvent instanceof ChatTitleChangedEvent) {
        $updatedInstruction = new NewMessageBody('Изменение названия чата получено.');
        $updatedInstruction->setAttachments([]);
        $client->editMessage($titleMessageId, $updatedInstruction);
        Console::success('Событие изменения названия чата получено и проверено.');

        break;
    }
    $chatTitle = $client->getChat($chatId)->getTitle()
        ?? throw new LogicException('Не удалось получить название чата.');
    if ($chatTitle !== $originalTitle) {
        continue;
    }
    $titleEvent->answer('Название изменилось, но событие не получено. Подробнее в консоли.');
    ManualTestReport::fail('Событие изменения названия чата', 'Название изменилось, но событие не получено.');

    break;
} while (true);

// Проверка получения сообщения пользователя с правом чтения.
$readText = 'Проверка чтения SDK ' . ManualEnvironment::getTestRunId();
$readMessage = new NewMessageBody('Нажмите кнопку, чтобы отправить сообщение в чат.');
$readMessage->addInlineKeyboard()->addMessageButton($readText);
Max::sendMessageToTestChat($readMessage);
$messageValidator = static fn(MessageCreatedEvent $event): bool
    => $event->getMessage()->getRecipient()->getChatId() === $chatId
    && $event->getMessage()->getText() === $readText;
Max::waitForMessageCreatedEvent($messageValidator, ChatType::Chat);
Console::success('Право чтения и получение сообщения пользователя проверены успешно.');

$heading = 'Этап 2 завершён';
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
