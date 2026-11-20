<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

use LogicException;
use MaxMessenger\Bot\Exception\ModifyMembersError;
use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\HttpResponseException;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\CommentRemovedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageEditedEvent;
use MaxMessenger\Bot\MaxBot\Event\UserAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\UserRemovedFromChatEvent;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\ChatStatus;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\MessageLinkType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\ChatAdmin;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\NewCommentBody;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Tests\Manual\Support\ChannelPermissions;
use MaxMessenger\Bot\Tests\Manual\Support\Console;
use MaxMessenger\Bot\Tests\Manual\Support\ManualEnvironment;
use MaxMessenger\Bot\Tests\Manual\Support\ManualTestReport;
use MaxMessenger\Bot\Tests\Manual\Support\Max;
use RuntimeException;

use function array_keys;
use function count;
use function defined;
use function dirname;
use function implode;
use function in_array;
use function is_array;
use function mb_strlen;

/**
 * Список тестов:
 * - Определение пользователя по пересланному посту.
 * - Определение тестового канала.
 * - Проверка отсутствия прав администратора у бота.
 * - Проверка типа тестового канала.
 * - Проверка статуса тестового канала.
 * - Проверка списка полей информации о тестовом канале.
 * - Проверка названия тестового канала.
 * - Проверка прав бота в канале.
 * - Отправка поста ботом.
 * - Редактирование поста бота.
 * - Закрепление поста.
 * - Открепление поста.
 * - Создание комментария ботом.
 * - Проверка списка полей отправленного комментария бота.
 * - Получение комментария по ID.
 * - Редактирование комментария бота.
 * - Получение комментариев по ID.
 * - Получение комментариев поста.
 * - Событие создания комментария пользователем.
 * - Получение комментария пользователя по ID.
 * - Цитирование комментария пользователя ботом.
 * - Получение ответа бота по ID.
 * - Событие редактирования комментария пользователя.
 * - Получение изменённого комментария пользователя по ID.
 * - Событие создания ответа пользователя на комментарий бота.
 * - Событие удаления ответа пользователя.
 * - Удаление комментария пользователя ботом.
 * - Удаление ответа бота.
 * - Удаление комментария бота.
 * - Удаление поста бота.
 * - Получение поста пользователя.
 * - Редактирование поста пользователя ботом.
 * - Отсутствие события редактирования поста ботом.
 * - Удаление поста пользователя ботом.
 * - Изменение названия канала.
 * - Изменение описания канала.
 * - Восстановление названия канала.
 * - Восстановление описания канала.
 * - Событие добавления подписчика.
 * - Назначение подписчика администратором.
 * - Снятие прав администратора с подписчика.
 * - Событие удаления подписчика.
 * - Проверка отказа при добавлении подписчика ботом.
 * - Удаление подписчика ботом.
 * - Выход бота из канала.
 * - Отправка форматированного сообщения о завершении этапа.
 *
 * Используемые методы API:
 * - getSubscriptions
 * - getMyInfo
 * - getUpdates
 * - sendMessageToUser (тестируется)
 * - answerOnCallback (тестируется)
 * - getChat (тестируется)
 * - getMembership
 * - editChat (тестируется)
 * - sendMessageToChat (тестируется)
 * - sendMessage (тестируется)
 * - editMessage (тестируется)
 * - getMessageById (тестируется)
 * - deleteMessage (тестируется)
 * - pinMessage (тестируется)
 * - getPinnedMessage (тестируется)
 * - unpinMessage (тестируется)
 * - sendComment (тестируется)
 * - getCommentById (тестируется)
 * - getCommentsById (тестируется)
 * - getCommentsFromPost (тестируется)
 * - getComments (тестируется)
 * - editComment (тестируется)
 * - deleteComment (тестируется)
 * - getMembers
 * - addMembers (тестируется)
 * - removeMember (тестируется)
 * - leaveChat (тестируется)
 * - getAdmins
 * - postAdmins (тестируется)
 * - deleteAdmin (тестируется)
 * - deleteAdmins (тестируется)
 *
 * Используемые события API:
 * - message_created (тестируется)
 * - message_edited (тестируется)
 * - message_callback (тестируется)
 * - bot_added
 * - bot_admin_permissions_changed
 * - comment_created (тестируется)
 * - comment_edited (тестируется)
 * - comment_removed (тестируется)
 * - user_added (тестируется)
 * - user_removed (тестируется)
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (in_array('--help', $argv, true)) {
    $console = Console::console();
    $console->title('Ручной тест канала и комментариев');
    $console->text('Запуск: php tests/Manual/channel-interaction.php');
    $console->note('Нужны канал с комментариями, бот-администратор и человек с правом менять права бота.');

    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
$client = Max::apiClient();
$botId = Max::getMyInfo()->getUserId();
$runId = ManualEnvironment::getTestRunId();

// Определение тестового канала.
$instruction = 'Добавьте бота в тестовый канал без прав администратора, включите комментарии и перешлите боту '
    . 'в личный диалог любой пост из этого канала.';
if (Max::hasTestUser()) {
    Max::instruction($instruction);
} else {
    Console::instruction($instruction);
}
do {
    $forward = Max::waitForMessageCreatedEvent(static function (MessageCreatedEvent $event): bool {
        $link = $event->getMessage()->getLink();

        return $link?->getType() === MessageLinkType::Forward
            && $link?->getChatId() !== null
            && $event->getMessage()->getSender()?->isBot() === false;
    }, userId: null);
    Max::identifyTestUser($forward);
    $channelId = $forward->getMessage()->getLink()?->getChatId()
        ?? throw new LogicException('Не определён ID канала.');
    $channel = null;
    do {
        try {
            $channel = $client->getChat($channelId);
        } catch (NotFoundException|ForbiddenException) {
            Max::instruction('Добавьте бота в канал, из которого переслан пост, без прав администратора.');
            $addedValidator = static fn(BotAddedToChatEvent $event): bool
                => $event->getChatId() === $channelId && $event->isChannel();
            Max::waitForEvent([UpdateType::BotAdded], $addedValidator, userId: null);
        }
    } while ($channel === null);
    if ($channel->getType() !== ChatType::Channel) {
        Max::instruction('Пересланное сообщение получено не из канала. Перешлите пост из тестового канала.');
    }
} while ($channel->getType() !== ChatType::Channel);
Console::success('Тестовый канал определён.');

// Проверка отсутствия прав администратора у бота.
$permissionValidator = static fn(BotAdminPermissionsChangedEvent $event): bool
    => $event->getChatId() === $channelId && $event->getBotId() === $botId;
if ($client->getMembership($channelId)->isAdmin()) {
    do {
        Max::instruction('Снимите права администратора с бота в тестовом канале.');
        $event = Max::waitForEvent([UpdateType::BotAdminPermissionsChanged], $permissionValidator, userId: null);
    } while ($event->isAdmin());
}
Console::success('У бота нет прав администратора в канале.');

// Проверка информации о тестовом канале.
$channel = $client->getChat($channelId);
$channelData = $channel->getRawData();
Console::json($channelData);
unset($channelData['description']);
if ($channel->getType() !== ChatType::Channel) {
    ManualTestReport::fail('Тип канала', 'Ожидался тип channel.');
}
if ($channel->getStatus() !== ChatStatus::Active) {
    ManualTestReport::fail('Статус канала', 'Ожидался статус active.');
}
$expectedFields = [
    'chat_id',
    'type',
    'status',
    'last_event_time',
    'participants_count',
    'is_public',
    'title',
    'link',
    'messages_count',
];
$fieldsMatch = Console::compareArrays($expectedFields, array_keys($channelData), sort: true);
if (!$fieldsMatch) {
    ManualTestReport::fail('Информация о канале', 'Список полей не совпал с ожидаемым.');
}
if (($channel->getTitle() ?? '') === '') {
    ManualTestReport::fail('Название канала', 'Название канала отсутствует или пустое.');
}
if (
    $fieldsMatch
    && $channel->getType() === ChatType::Channel
    && $channel->getStatus() === ChatStatus::Active
    && ($channel->getTitle() ?? '') !== ''
) {
    Console::success('Информация о тестовом канале проверена.');
}

// Проверка прав бота в канале.
$requiredPermissions = [
    ChatAdminPermission::ReadAllMessages,
    ChatAdminPermission::Write,
    ChatAdminPermission::Edit,
    ChatAdminPermission::Delete,
    ChatAdminPermission::PinMessage,
    ChatAdminPermission::AddRemoveMembers,
    ChatAdminPermission::AddAdmins,
    ChatAdminPermission::ChangeChatInfo,
];
do {
    $membership = $client->getMembership($channelId);
    $missingPermissions = [];
    foreach ($requiredPermissions as $permission) {
        if (!$membership->hasPermission($permission)) {
            $missingPermissions[] = ChannelPermissions::label($permission);
        }
    }
    if ($missingPermissions !== []) {
        Max::instruction(
            'Назначьте бота администратором канала и добавьте ему права: %s.',
            implode(', ', $missingPermissions),
        );
        Max::waitForEvent([UpdateType::BotAdminPermissionsChanged], $permissionValidator, userId: null);
    }
} while ($missingPermissions !== []);
Console::success('Права бота в канале проверены.');

// Проверка отправки поста ботом.
$postBody = new NewMessageBody("Пост SDK $runId");
$post = Max::sendMessageToChannel($channelId, $postBody);
$postId = $post->getBody()->getMid();
if (Max::isSentMessageValid()) {
    Console::success('Отправка поста бота проверена.');
}

// Проверка редактирования поста бота.
Max::instruction('В тестовом канале нажмите кнопку «Отредактировать пост».');
$controlId = Max::sendMessageToChannelAndWaitForCallback(
    $channelId,
    'Нажмите кнопку, чтобы отредактировать тестовый пост.',
    'Отредактировать пост',
);
$editedPostText = "Отредактированный пост SDK $runId";
$client->editMessage($postId, new NewMessageBody($editedPostText));
$actualPostText = $client->getMessageById($postId)->getText();
if ($actualPostText !== $editedPostText) {
    Console::diff($editedPostText, $actualPostText);
    ManualTestReport::fail('Редактирование поста бота', 'Текст поста не совпал с ожидаемым.');
}
Max::instruction('Ответьте на вопрос «Пост отредактирован?» в тестовом канале.');
if (!Max::askInExistingMessage($controlId, 'Пост отредактирован?')) {
    ManualTestReport::fail('Редактирование поста бота', 'Пользователь отметил проверку как непройденную.');
} elseif ($actualPostText === $editedPostText) {
    Console::success('Редактирование поста бота проверено.');
}

// Проверка закрепления поста.
$client->pinMessage($channelId, $postId);
$postPinned = $client->getPinnedMessage($channelId)->getMessage()?->getBody()->getMid() === $postId;
if (!$postPinned) {
    ManualTestReport::fail('Закрепление поста', 'Тестовый пост не стал закреплённым.');
}
Max::instruction('Ответьте на вопрос «Пост закреплён?» в тестовом канале.');
if (!Max::askInExistingMessage($controlId, 'Пост закреплён?')) {
    ManualTestReport::fail('Закрепление поста', 'Пользователь отметил проверку как непройденную.');
} elseif ($postPinned) {
    Console::success('Закрепление поста проверено.');
}

// Проверка открепления поста.
$client->unpinMessage($channelId);
$postUnpinned = $client->getPinnedMessage($channelId)->getMessage() === null;
if (!$postUnpinned) {
    ManualTestReport::fail('Открепление поста', 'Тестовый пост остался закреплённым.');
}
Max::instruction('Ответьте на вопрос «Пост откреплён?» в тестовом канале.');
if (!Max::askInExistingMessage($controlId, 'Пост откреплён?')) {
    ManualTestReport::fail('Открепление поста', 'Пользователь отметил проверку как непройденную.');
} elseif ($postUnpinned) {
    Console::success('Открепление поста проверено.');
}

# Проверка создания комментария ботом.
$botComment = $client->sendComment($postId, "Комментарий бота SDK $runId")->getMessage();
$botCommentId = $botComment->getBody()->getMid();
$sentCommentData = $botComment->getRawData();
Console::json($sentCommentData);
$expectedCommentFields = [
    'recipient',
    'timestamp',
    'body',
    'sender',
];
$commentFieldsMatch = Console::compareArrays($expectedCommentFields, array_keys($sentCommentData), sort: true);
if ($commentFieldsMatch) {
    Console::success('Список полей комментария бота проверен.');
} else {
    ManualTestReport::fail('Поля комментария бота', 'Список полей не совпал с ожидаемым.');
}
if (!Max::askInExistingMessage($controlId, 'Комментарий бота появился под предыдущим постом?')) {
    ManualTestReport::fail('Создание комментария бота', 'Пользователь отметил проверку как непройденную.');
} elseif ($commentFieldsMatch) {
    Console::success('Создание комментария бота проверено.');
}

// Проверка получения комментария бота по ID.
$actualComment = $client->getCommentById($postId, $botCommentId)->getRawData();
if (isset($sentCommentData['sender']) && is_array($sentCommentData['sender'])) {
    unset($sentCommentData['sender']['last_activity_time']);
}
if (isset($actualComment['sender']) && is_array($actualComment['sender'])) {
    unset($actualComment['sender']['last_activity_time']);
}
if (Console::compareArrays($sentCommentData, $actualComment)) {
    Console::success('Получение комментария бота по ID проверено.');
} else {
    ManualTestReport::fail('Получение комментария бота', 'Комментарий не совпал с отправленным.');
}

// Проверка редактирования комментария бота.
$editedCommentText = "Изменённый комментарий бота SDK $runId";
$client->editComment($postId, $botCommentId, $editedCommentText);
$actualCommentText = $client->getCommentById($postId, $botCommentId)->getText();
if ($actualCommentText !== $editedCommentText) {
    Console::diff($editedCommentText, $actualCommentText);
    ManualTestReport::fail('Редактирование комментария бота', 'Текст комментария не совпал с ожидаемым.');
}
if (!Max::askInExistingMessage($controlId, 'Комментарий бота изменился под постом?')) {
    ManualTestReport::fail('Редактирование комментария бота', 'Пользователь отметил проверку как непройденную.');
} elseif ($actualCommentText === $editedCommentText) {
    Console::success('Редактирование комментария бота проверено.');
}

// Проверка получения комментариев по ID.
if (count($client->getCommentsById($postId, [$botCommentId])->getMessages()) !== 1) {
    ManualTestReport::fail('Получение комментариев по ID', 'Ожидался один комментарий.');
} else {
    Console::success('Получение комментариев по ID проверено.');
}

// Проверка получения комментариев поста.
if ($client->getCommentsFromPost($postId)->getMessages() === []) {
    ManualTestReport::fail('Получение комментариев поста', 'Метод вернул пустой список.');
} else {
    Console::success('Получение комментариев поста проверено.');
}

// Проверка события создания комментария пользователем.
$client->editMessage($controlId, new NewMessageBody('Напишите любой комментарий под предыдущим постом.', []));
$commentValidator = static fn(CommentCreatedEvent $event): bool => $event->getPostId() === $postId;
$userCreated = Max::waitForEvent([UpdateType::CommentCreated], $commentValidator, userId: null);
$userCommentId = $userCreated->getComment()->getBody()->getMid();
Console::success('Событие создания комментария пользователем получено.');
if ($userCreated->getComment()->getSender() !== null) {
    ManualTestReport::fail('Отправитель комментария', 'Ожидался комментарий без отправителя.');
} else {
    Console::success('Комментарий получен без отправителя.');
}
$expectedComment = $userCreated->getComment()->getRawData();
$actualComment = $client->getCommentById($postId, $userCommentId)->getRawData();
if (!Console::compareArrays($expectedComment, $actualComment)) {
    ManualTestReport::fail('Получение комментария пользователя', 'Комментарий не совпал с событием.');
}

// Проверка цитирования комментария пользователя ботом.
$replyBody = new NewCommentBody("Ответ бота SDK $runId");
$replyBody->setReplyLink($userCommentId);
$botReply = $client->sendComment($postId, $replyBody)->getMessage();
$botReplyId = $botReply->getBody()->getMid();
$expectedReply = $botReply->getRawData();
$actualReply = $client->getCommentById($postId, $botReplyId)->getRawData();
if (isset($expectedReply['sender']) && is_array($expectedReply['sender'])) {
    unset($expectedReply['sender']['last_activity_time']);
}
if (isset($actualReply['sender']) && is_array($actualReply['sender'])) {
    unset($actualReply['sender']['last_activity_time']);
}
if (!Console::compareArrays($expectedReply, $actualReply)) {
    ManualTestReport::fail('Получение ответа бота', 'Ответ не совпал с отправленным.');
}
if ($botReply->getLink()?->getMessage()->getMid() !== $userCommentId) {
    ManualTestReport::fail('Цитирование комментария', 'Ответ бота не ссылается на комментарий пользователя.');
}
if (!Max::askInExistingMessage($controlId, 'Ответ бота находится под комментарием пользователя?')) {
    ManualTestReport::fail('Цитирование комментария', 'Пользователь отметил проверку как непройденную.');
} elseif ($botReply->getLink()?->getMessage()->getMid() === $userCommentId) {
    Console::success('Цитирование комментария пользователя ботом проверено.');
}

// Проверка события редактирования комментария пользователя.
$client->editMessage($controlId, new NewMessageBody('Отредактируйте свой комментарий, изменив его текст.', []));
$editCommentValidator = static fn(CommentEditedEvent $event): bool
    => $event->getComment()->getBody()->getMid() === $userCommentId;
$editedUserComment = Max::waitForEvent([UpdateType::CommentEdited], $editCommentValidator, userId: null);
Console::success('Событие редактирования комментария пользователя получено.');
if ($editedUserComment->getComment()->getSender() !== null) {
    ManualTestReport::fail('Отправитель изменённого комментария', 'Ожидался комментарий без отправителя.');
} else {
    Console::success('Изменённый комментарий получен без отправителя.');
}
if ($editedUserComment->getComment()->getText() === $userCreated->getComment()->getText()) {
    ManualTestReport::fail('Редактирование комментария пользователя', 'Текст комментария не изменился.');
}
$expectedComment = $editedUserComment->getComment()->getRawData();
$actualComment = $client->getCommentById($postId, $userCommentId)->getRawData();
if (!Console::compareArrays($expectedComment, $actualComment)) {
    ManualTestReport::fail('Получение изменённого комментария', 'Комментарий не совпал с событием.');
}

// Проверка события ответа пользователя на комментарий бота.
$client->editMessage($controlId, new NewMessageBody('Ответьте на любой комментарий бота любым текстом.'));
$replyValidator = static fn(CommentCreatedEvent $event): bool
    => in_array($event->getComment()->getLink()?->getMessage()->getMid(), [$botCommentId, $botReplyId], true);
$userReply = Max::waitForEvent([UpdateType::CommentCreated], $replyValidator, userId: null);
$userReplyId = $userReply->getComment()->getBody()->getMid();
Console::success('Событие ответа пользователя получено.');
if ($userReply->getComment()->getSender() !== null) {
    ManualTestReport::fail('Отправитель ответа на комментарий', 'Ожидался комментарий без отправителя.');
} else {
    Console::success('Ответ на комментарий получен без отправителя.');
}

// Проверка события удаления ответа пользователя.
$client->editMessage($controlId, new NewMessageBody('Удалите собственный ответ на комментарий бота.'));
Max::waitForEvent(
    [UpdateType::CommentRemoved],
    static fn(CommentRemovedEvent $event): bool => $event->getMessageId() === $userReplyId,
);
Console::success('Событие удаления ответа пользователя получено.');
try {
    $client->getCommentById($postId, $userReplyId);
    ManualTestReport::fail('Удаление ответа пользователя', 'Ответ по-прежнему доступен.');
} catch (NotFoundException) {
    Console::success('Удаление ответа пользователя проверено.');
}

// Проверка удаления комментария пользователя ботом.
$client->deleteComment($postId, $userCommentId);
try {
    $client->getCommentById($postId, $userCommentId);
    ManualTestReport::fail('Удаление комментария пользователя', 'Комментарий по-прежнему доступен.');
} catch (NotFoundException) {
    Console::success('Удаление комментария пользователя ботом проверено.');
}

// Проверка удаления ответа бота.
$client->deleteComment($postId, $botReplyId);
try {
    $client->getCommentById($postId, $botReplyId);
    ManualTestReport::fail('Удаление ответа бота', 'Ответ по-прежнему доступен.');
} catch (NotFoundException) {
    Console::success('Удаление ответа бота проверено.');
}

// Проверка удаления комментария бота.
$client->deleteComment($postId, $botCommentId);
try {
    $client->getCommentById($postId, $botCommentId);
    ManualTestReport::fail('Удаление комментария бота', 'Комментарий по-прежнему доступен.');
} catch (NotFoundException) {
    Console::success('Удаление комментария бота проверено.');
}

// Проверка удаления поста бота.
$client->deleteMessage($postId);
try {
    $client->getMessageById($postId);
    ManualTestReport::fail('Удаление поста бота', 'Пост по-прежнему доступен.');
} catch (NotFoundException) {
    Console::success('Удаление поста бота проверено.');
}

// Проверка получения поста пользователя.
$client->editMessage($controlId, new NewMessageBody(
    'Опубликуйте в тестовом канале отдельный пост с любым текстом. Бот изменит и удалит этот тестовый пост.',
));
$postValidator = static fn(MessageCreatedEvent $event): bool
    => $event->getMessage()->getRecipient()->getChatId() === $channelId;
$userPostUpdate = Max::waitForMessageCreatedEvent($postValidator, ChatType::Channel, userId: null);
$userPostId = $userPostUpdate->getMessage()->getBody()->getMid();
Console::success('Пост пользователя получен.');
if ($userPostUpdate->getMessage()->getSender() !== null) {
    ManualTestReport::fail('Отправитель поста', 'Ожидался пост без отправителя.');
} else {
    Console::success('Пост получен без отправителя.');
}

// Проверка редактирования поста пользователя ботом.
$editedUserPostText = "Изменённый пост пользователя SDK {$runId}";
$client->editMessage($userPostId, $editedUserPostText);
if ($client->getMessageById($userPostId)->getText() !== $editedUserPostText) {
    throw new RuntimeException('Бот не изменил тестовый пост пользователя.');
}
Console::success('Редактирование поста пользователя ботом проверено.');
$question = new NewMessageBody('Пост изменён?');
$question
    ->addInlineKeyboard()
    ->addCallbackButton('✅ Да', 'manual:yes')
    ->addCallbackButton('❌ Нет', 'manual:no');
$client->editMessage($controlId, $question);
$eventValidator = static function (BaseEvent $event) use ($controlId, $userPostId): bool {
    if ($event instanceof MessageEditedEvent) {
        return $event->getMessage()->getBody()->getMid() === $userPostId;
    }

    return $event instanceof MessageCallbackEvent
        && $event->getUserId() === Max::getTestUserId()
        && $event->getMessage()->getBody()->getMid() === $controlId
        && in_array($event->getCallback()->getPayload(), ['manual:yes', 'manual:no'], true);
};
do {
    $event = Max::waitForEvent([UpdateType::MessageEdited, UpdateType::MessageCallback], $eventValidator, userId: null);
    if ($event instanceof MessageEditedEvent) {
        ManualTestReport::fail('Событие собственного действия', 'Бот получил событие на собственное действие.');
        $question = new NewMessageBody(
            "<mark>🔴 Ошибка: бот получил событие собственного действия.</mark>\nПост изменён?",
            format: TextFormat::Html,
        );
        $question
            ->addInlineKeyboard()
            ->addCallbackButton('✅ Да', 'manual:yes')
            ->addCallbackButton('❌ Нет', 'manual:no');
        $client->editMessage($controlId, $question);
    }
} while (!$event instanceof MessageCallbackEvent);
if ($event->getCallback()->getPayload() === 'manual:no') {
    ManualTestReport::fail('Редактирование поста пользователя', 'Пользователь не подтвердил изменение поста.');
}

// Проверка удаления поста пользователя ботом.
$client->deleteMessage($userPostId);
Console::success('Удаление поста пользователя ботом проверено.');

// Проверка изменения и восстановления названия и описания канала.
$originalTitle = $channel->getTitle();
if ($originalTitle === null) {
    throw new RuntimeException('Не удалось получить исходное название канала.');
}
$originalDescription = $channel->getDescription() ?? '';
$testTitle = "SDK канал {$runId}";
$testDescription = "Проверка редактирования канала SDK {$runId}";
try {
    $client->editChat($channelId, ChatPatch::make(title: $testTitle, description: $testDescription, notify: false));
    $editedChannel = $client->getChat($channelId);
    if ($editedChannel->getTitle() !== $testTitle || $editedChannel->getDescription() !== $testDescription) {
        throw new RuntimeException('Название или описание канала не изменилось.');
    }
} finally {
    $client->editChat($channelId, ChatPatch::make(
        title: $originalTitle,
        description: $originalDescription,
        notify: false,
    ));
}
$restoredChannel = $client->getChat($channelId);
if (
    $restoredChannel->getTitle() !== $originalTitle
    || ($restoredChannel->getDescription() ?? '') !== $originalDescription
) {
    throw new RuntimeException('Исходные название и описание канала не восстановились.');
}
Console::success('Название и описание канала изменены и восстановлены.');

// Проверка события добавления подписчика.
$client->editMessage($controlId, new NewMessageBody(
    'Добавьте в тестовый канал второго тестового подписчика, которого можно временно назначить '
    . 'администратором и удалить из канала.',
    [],
));
$isTestSubscriber = static fn(UserAddedToChatEvent $event): bool => $event->getChatId() === $channelId;
$subscriberAdded = Max::waitForEvent([UpdateType::UserAdded], $isTestSubscriber, userId: null);
$subscriberId = $subscriberAdded->getUserId();
$member = $client->getMembers($channelId, [$subscriberId])->getMembers()[0] ?? null;
if ($member?->getUserId() !== $subscriberId) {
    throw new RuntimeException('Добавленный человеком подписчик не найден в канале.');
}
Console::success('Событие добавления подписчика получено.');

// Проверка назначения и снятия прав администратора.
$client->postAdmins($channelId, [ChatAdmin::make($subscriberId, [ChatAdminPermission::ChangeChatInfo])]);
$foundAdmin = false;
foreach ($client->getAdmins($channelId)->getMembers() as $admin) {
    $foundAdmin = $foundAdmin || $admin->getUserId() === $subscriberId;
}
if (!$foundAdmin) {
    throw new RuntimeException('Назначенный ботом администратор канала не найден.');
}
if (!Max::askInExistingMessage(
    $controlId,
    "Бот назначил подписчика ID {$subscriberId} администратором. Он появился в списке администраторов канала?",
)) {
    ManualTestReport::fail('Назначение администратора канала', 'Пользователь отметил проверку как непройденную.');
}
$client->deleteAdmin($channelId, $subscriberId);
foreach ($client->getAdmins($channelId)->getMembers() as $admin) {
    if ($admin->getUserId() === $subscriberId) {
        throw new RuntimeException('После снятия прав подписчик остался администратором канала.');
    }
}
if (!Max::askInExistingMessage($controlId, "Подписчик ID {$subscriberId} исчез из списка администраторов канала?")) {
    ManualTestReport::fail('Снятие прав администратора канала', 'Пользователь отметил проверку как непройденную.');
}

// Проверка события удаления подписчика.
$client->editMessage(
    $controlId,
    new NewMessageBody("Удалите подписчика ID {$subscriberId} из канала."),
);
$removedValidator = static fn(UserRemovedFromChatEvent $event): bool => $event->getChatId() === $channelId;
$removedEvent = Max::waitForEvent([UpdateType::UserRemoved], $removedValidator, userId: null);
Console::success('Событие удаления подписчика получено.');
if ($removedEvent->getAdminId() !== Max::getTestUserId()) {
    ManualTestReport::fail(
        'Администратор, удаливший подписчика',
        'ID администратора не совпал с ID пользователя теста.',
    );
} else {
    Console::success('ID администратора, удалившего подписчика, проверен.');
}
if ($removedEvent->getUserId() !== $subscriberId) {
    ManualTestReport::fail('Удалённый подписчик', 'ID удалённого пользователя не совпал с ID тестового подписчика.');
} else {
    Console::success('ID удалённого подписчика проверен.');
}

// Проверка отказа при добавлении подписчика ботом.
try {
    $client->addMembers($channelId, [$subscriberId]);
    ManualTestReport::fail('Добавление подписчика ботом', 'Добавление подписчика в канал не было отклонено.');
} catch (ModifyMembersError $exception) {
    $expectedError = [
        'success' => false,
        'failed_user_ids' => [$subscriberId],
        'failed_user_details' => [
            [
                'error_code' => 'add.participant.privacy',
                'user_ids' => [$subscriberId],
            ],
        ],
    ];
    $actualError = $exception->getResult()->getRawData();
    if (!Console::compareArrays($expectedError, $actualError)) {
        ManualTestReport::fail('Добавление подписчика ботом', 'Raw-данные ошибки не совпали с ожидаемыми.');
    } else {
        Console::success('Отказ в добавлении подписчика ботом проверен.');
    }
} catch (HttpResponseException|SimpleQueryError $exception) {
    ManualTestReport::fail('Добавление подписчика ботом', $exception);
}
$member = $client->getMembers($channelId, [$subscriberId])->getMembers()[0] ?? null;
if ($member?->getUserId() === $subscriberId) {
    try {
        $client->removeMember($channelId, $subscriberId);
        $member = $client->getMembers($channelId, [$subscriberId])->getMembers()[0] ?? null;
        if ($member?->getUserId() === $subscriberId) {
            ManualTestReport::fail('Удаление добавленного подписчика', 'Подписчик остался в канале.');
        }
    } catch (HttpResponseException|SimpleQueryError $exception) {
        ManualTestReport::fail('Удаление добавленного подписчика', $exception);
        $member = $client->getMembers($channelId, [$subscriberId])->getMembers()[0] ?? null;
    }
}
if ($member?->getUserId() !== $subscriberId) {
    $client->editMessage($controlId, new NewMessageBody(
        'Добавьте любого подписчика в канал вручную, затем бот попробует его удалить.',
        [],
    ));
    $addedValidator = static fn(UserAddedToChatEvent $event): bool => $event->getChatId() === $channelId;
    $addedSubscriber = Max::waitForEvent([UpdateType::UserAdded], $addedValidator, userId: null);
    $subscriberId = $addedSubscriber->getUserId();
}

// Проверка удаления подписчика ботом.
try {
    $client->removeMember($channelId, $subscriberId);
    $members = $client->getMembers($channelId, [$subscriberId])->getMembers();
    if ($members !== []) {
        ManualTestReport::fail('Удаление подписчика ботом', 'Подписчик остался в канале.');
    } else {
        Console::success('Удаление подписчика ботом проверено успешно.');
    }
} catch (HttpResponseException|SimpleQueryError $exception) {
    ManualTestReport::fail('Удаление подписчика ботом', $exception);
    $members = $client->getMembers($channelId, [$subscriberId])->getMembers();
}
if ($members !== []) {
    $client->editMessage($controlId, new NewMessageBody(
        "Удалите тестового подписчика ID {$subscriberId} вручную после завершения сценария.",
    ));
} else {
    $client->deleteMessage($controlId);
}

// Выход бота из канала после завершения проверок.
$client->leaveChat($channelId);
Console::success('Бот вышел из тестового канала.');

$heading = 'Этап 6 завершён';
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
