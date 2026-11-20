<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual;

use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Enum\AttachmentType;
use MaxMessenger\Bot\Model\Enum\MarkupElementType;
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Response\ContactAttachment;
use MaxMessenger\Bot\Model\Response\FileAttachment;
use MaxMessenger\Bot\Model\Response\LocationAttachment;
use MaxMessenger\Bot\Model\Response\PhotoAttachment;
use MaxMessenger\Bot\Model\Response\ShareAttachment;
use MaxMessenger\Bot\Model\Response\StickerAttachment;
use MaxMessenger\Bot\Model\Response\VideoAttachment;
use MaxMessenger\Bot\Tests\Manual\Support\Console;
use MaxMessenger\Bot\Tests\Manual\Support\ManualEnvironment;
use MaxMessenger\Bot\Tests\Manual\Support\ManualTestReport;
use MaxMessenger\Bot\Tests\Manual\Support\Max;

use function array_filter;
use function count;
use function defined;
use function dirname;
use function implode;
use function in_array;
use function mb_strlen;
use function sprintf;
use function str_starts_with;
use function substr;

/**
 * Список тестов:
 * - Определение пользователя по сообщению в личном диалоге.
 * - Отправка обычного сообщения.
 * - Форматирование текста в Markdown.
 * - Форматирование текста в HTML.
 * - Регистрация команд без слеша, с одним и двумя слешами.
 * - Отображение зарегистрированных команд в подсказках.
 * - Изменение команд.
 * - Очистка списка команд.
 * - Кнопка копирования.
 * - Получение стикера.
 * - Отправка стикера.
 * - Получение события callback-кнопки.
 * - Изменение сообщения после ответа на callback.
 * - Изменение тестового сообщения.
 * - Изменение сообщения с кнопкой.
 * - Удаление тестового сообщения.
 * - Удаление сообщения с кнопкой.
 * - Кнопка открытия ссылки.
 * - Кнопка отправки сообщения.
 * - Кнопка запроса собственного контакта.
 * - Получение стороннего контакта с номером телефона.
 * - Отправка стороннего контакта с номером телефона.
 * - Кнопка запроса геолокации в обычном режиме.
 * - Кнопка запроса геолокации в быстром режиме.
 * - Отправка полученной геолокации.
 * - Отправка ссылки с вложением Share.
 * - Автоматическое добавление вложения Share для ссылки.
 * - Запрет добавления вложения Share для ссылки.
 * - Получение изображения.
 * - Получение видео.
 * - Получение файла.
 * - Отправка полученного файла.
 * - Отправка полученных изображения и видео одним сообщением.
 * - Отправка форматированного сообщения о завершении этапа в личный диалог.
 *
 * Используемые методы API:
 * - getSubscriptions
 * - getMyInfo (тестируется)
 * - editMyCommands (тестируется)
 * - sendMessage (тестируется)
 * - sendMessageToUser (тестируется)
 * - getMessageById (тестируется)
 * - editMessage (тестируется)
 * - deleteMessage (тестируется)
 * - answerOnCallback (тестируется)
 * - getUpdates
 * - validateContactAttachmentHash (тестируется)
 *
 * Используемые события API:
 * - message_created (тестируется)
 * - message_callback (тестируется)
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (in_array('--help', $argv, true)) {
    $console = Console::console();
    $console->title('Ручной тест диалога, кнопок и команд бота');
    $console->text('Запуск: php tests/Manual/bot-interaction.php');
    $console->note('Нужны токен бота, личный диалог с ним и отсутствие Webhook-подписок.');

    exit(0);
}

ManualEnvironment::initializeExceptionHandler();
$client = Max::apiClient();

// Определение пользователя теста по сообщению в личном диалоге.
Max::startDialogTest();
$runId = ManualEnvironment::getTestRunId();
Console::success('Определение пользователя теста прошло успешно.');

// Отправка обычного сообщения пользователю.
$plainMessage = new NewMessageBody('1/3 Обычное сообщение. Код проверки: ' . $runId);
Max::sendMessageToTestUser($plainMessage);
if (Max::isSentMessageValid()) {
    Console::success('Отправка обычного сообщения прошла успешно.');
}

// Проверка форматирования текста и полного RAW ответа для каждого формата.
$prefix = '2/3 Formatting. Code: ' . $runId;
$userName = Max::getTestUser()->getFullName();
$text = implode("\n", [
    $prefix,
    'bold',
    'italic',
    'strike',
    'underline',
    'code',
    'highlight',
    'heading',
    'heading2',
    'heading3',
    'heading4',
    'quote',
    'link',
    $userName,
]);
$markup = [
    ['from' => 31, 'length' => 4, 'type' => MarkupElementType::Strong->value],
    ['from' => 36, 'length' => 6, 'type' => MarkupElementType::Emphasized->value],
    ['from' => 43, 'length' => 6, 'type' => MarkupElementType::Strikethrough->value],
    ['from' => 50, 'length' => 9, 'type' => MarkupElementType::Underline->value],
    ['from' => 60, 'length' => 4, 'type' => MarkupElementType::Monospaced->value],
    ['from' => 65, 'length' => 9, 'type' => MarkupElementType::Highlighted->value],
    ['from' => 75, 'length' => 7, 'level' => 1, 'type' => MarkupElementType::Heading->value],
    ['from' => 83, 'length' => 8, 'level' => 2, 'type' => MarkupElementType::Heading->value],
    ['from' => 92, 'length' => 8, 'level' => 3, 'type' => MarkupElementType::Heading->value],
    ['from' => 101, 'length' => 8, 'level' => 4, 'type' => MarkupElementType::Heading->value],
    ['from' => 110, 'length' => 5, 'type' => MarkupElementType::Quote->value],
    ['url' => 'https://dev.max.ru/', 'from' => 116, 'length' => 4, 'type' => MarkupElementType::Link->value],
    [
        'from' => 121,
        'length' => mb_strlen($userName),
        'user_id' => Max::getTestUserId(),
        'type' => MarkupElementType::UserMention->value,
    ],
];
$markdown = implode("\n", [
    $prefix,
    '**bold**',
    '*italic*',
    '~~strike~~',
    '++underline++',
    '`code`',
    '^^highlight^^',
    '# heading',
    '## heading2',
    '### heading3',
    '#### heading4',
    '> quote',
    '',
    '[link](https://dev.max.ru/)',
    Max::getTestUser()->getMarkdownLink(),
]);
Max::sendFormattedTextToTestUser(
    new NewMessageBody($markdown, format: TextFormat::Markdown),
    $text,
    $markup,
);
$markdownPassed = Max::isSentMessageValid();
$html = implode("\n", [
    $prefix,
    '<b>bold</b>',
    '<i>italic</i>',
    '<s>strike</s>',
    '<u>underline</u>',
    '<code>code</code>',
    '<mark>highlight</mark>',
    '<h1>heading</h1>',
    '<h2>heading2</h2>',
    '<h3>heading3</h3>',
    '<h4>heading4</h4>',
    '<blockquote>quote</blockquote>',
    '<a href="https://dev.max.ru/">link</a>',
    Max::getTestUser()->getHtmlLink(),
]);
Max::sendFormattedTextToTestUser(new NewMessageBody($html, format: TextFormat::Html), $text, $markup);
if ($markdownPassed && Max::isSentMessageValid()) {
    Console::success('Форматирование Markdown и HTML прошло успешно.');
}
Console::announceDialogInstructions();

// Проверка регистрации, изменения и очистки команд бота.
$updateAndCheckCommands = static function (array $expectedCommands, string $checkName) use ($client): void {
    /**
     * @var list<BotCommand> $expectedCommands
     * @var non-empty-string $checkName
     */
    $result = $client->editMyCommands(BotCommandsPatch::make($expectedCommands));
    $myInfo = $client->getMyInfo();
    $commands = [];
    foreach ($expectedCommands as $command) {
        $name = $command->getName();
        if (str_starts_with($name, '/')) {
            $name = substr($name, 1);
        }
        $commands[] = [
            'name' => $name,
            'description' => $command->getDescription(),
        ];
    }
    $expectedCommandsInfo = $commands === [] ? [] : ['commands' => $commands];
    $baseline = Max::getMyInfo();
    $expectedBotInfo = array_filter([
        'user_id' => $baseline->getUserId(),
        'first_name' => $baseline->getFirstName(),
        'is_bot' => true,
        'username' => $baseline->getUsername(),
        'last_activity_time' => $myInfo->getLastActivityTimeRaw(),
        'description' => $baseline->getDescription(),
        'avatar_url' => $baseline->getAvatarUrl(),
        'full_avatar_url' => $baseline->getFullAvatarUrl(),
    ], static fn(mixed $value): bool => $value !== null);
    if ($commands !== []) {
        $expectedBotInfo['commands'] = $commands;
    }
    $actualCommandsInfo = $result->getRawData();
    $actualBotInfo = $myInfo->getRawData();
    unset($actualBotInfo['name']);
    $passed = true;
    if (!Console::compareArrays($expectedCommandsInfo, $actualCommandsInfo)) {
        $passed = false;
        ManualTestReport::fail(
            $checkName . ': editMyCommands',
            'Ответ не совпал с ожидаемым.',
            notifyUser: true,
        );
    }
    if (!Console::compareArrays($expectedBotInfo, $actualBotInfo)) {
        $passed = false;
        ManualTestReport::fail(
            $checkName . ': getMyInfo',
            'Ответ не совпал с ожидаемым.',
            notifyUser: true,
        );
    }
    if ($passed) {
        Console::success($checkName . ': успешно.');
    }
};
try {
    $updateAndCheckCommands([
        BotCommand::make('/sdk' . $runId, 'Команда со слешем'),
        BotCommand::make('plain' . $runId, 'Команда без слеша'),
        BotCommand::make('//double' . $runId, 'Команда с двумя слешами'),
    ], 'Регистрация команд без слеша, с / и с //');
    if (Max::askInTestChat(
        'Введите «/» в поле сообщения. Видите ли вы новые команды бота в подсказках?',
        dialog: true,
    )) {
        Console::success('Отображение команд в подсказках проверено успешно.');
    } else {
        ManualTestReport::fail('Отображение команд в подсказках', 'Пользователь не увидел новые команды.');
    }
    $updateAndCheckCommands([
        BotCommand::make('/sdk' . $runId, 'Изменённое описание команды'),
        BotCommand::make('updated' . $runId, 'Новая команда вместо предыдущей'),
    ], 'Изменение команд');
} finally {
    $updateAndCheckCommands([], 'Очистка списка команд');
}

// Проверка кнопки копирования.
$emojiInstruction = 'Нажмите кнопку «Скопировать эмодзи», вставьте эмодзи в чат и отправьте боту.';
$emojiInstructionBody = new NewMessageBody($emojiInstruction);
$emojiInstructionBody->addInlineKeyboard()->addClipboardButton('Скопировать эмодзи', '👍');
Max::sendMessageToTestUser($emojiInstructionBody);
Max::waitForExactDialogText('👍');
Max::sendMessageToTestUser(new NewMessageBody('✅'));
Console::success('Кнопка копирования проверена успешно.');

// Проверка получения и отправки стикера.
Max::sendMessageToTestUser(new NewMessageBody('Отправьте боту стикер.'));
$stickerValidator = static function (MessageCreatedEvent $event): bool {
    $attachments = $event->getMessage()->getBody()->getAttachments();

    return $attachments !== null && count($attachments) === 1 && $attachments[0] instanceof StickerAttachment;
};
$stickerEvent = Max::waitForMessageCreatedEvent($stickerValidator);
/** @var StickerAttachment $incomingSticker */
$incomingSticker = $stickerEvent->getMessage()->getBody()->getAttachments()[0] ?? null;
$stickerBody = new NewMessageBody();
$stickerBody->addStickerAttachment($incomingSticker->getPayload()->getCode());
Max::sendMessageToTestUser($stickerBody);
if (Max::isSentMessageValid()) {
    Console::success('Получение и отправка стикера прошли успешно.');
}

// Проверка callback-кнопки и изменения сообщения после ответа.
$callbackBody = new NewMessageBody(
    'Нажмите кнопку «Callback». После нажатия текст сообщения изменится, а кнопка исчезнет.',
);
$callbackBody->addInlineKeyboard()->addCallbackButton('Callback', 'manual:action');
$callbackMessage = Max::sendMessageToTestUser($callbackBody);
$callbackMessageId = $callbackMessage->getBody()->getMid();
$callbackEvent = Max::waitForMessageCallbackEvent($callbackMessageId);
$callbackAnswerText = 'Callback обработан.';
$callbackEvent->answer($callbackAnswerText);
$answeredMessage = $client->getMessageById($callbackMessageId);
$expectedCallbackMessage = [
    'text' => $callbackAnswerText,
    'attachments' => [],
];
$actualCallbackMessage = [
    'text' => $answeredMessage->getText(),
    'attachments' => $answeredMessage->getBody()->getRawData()['attachments'] ?? [],
];
$callbackPassed = Console::compareArrays($expectedCallbackMessage, $actualCallbackMessage);
if (!$callbackPassed) {
    ManualTestReport::fail(
        'Callback и изменение сообщения',
        'После ответа на callback исходное сообщение должно содержать текст «Callback обработан.» без кнопок.',
        notifyUser: true,
    );
}
if ($callbackPassed) {
    Console::success('Callback и изменение сообщения прошли успешно.');
}

// Проверка изменения сообщения и удаления двух сообщений кнопками.
$testMessage = Max::sendMessageToTestUser(new NewMessageBody('Тестовое сообщение. Код: ' . $runId));
$testMessageId = $testMessage->getBody()->getMid();
$controlBody = new NewMessageBody('Нажмите «Изменить», чтобы изменить текст тестового сообщения.');
$controlBody->addInlineKeyboard()->addCallbackButton('Изменить', 'manual:action');
$controlMessage = Max::sendMessageToTestUser($controlBody);
$controlMessageId = $controlMessage->getBody()->getMid();
$editEvent = Max::waitForMessageCallbackEvent($controlMessageId);
$editedText = 'Тестовое сообщение изменено. Код: ' . $runId;
$client->editMessage($testMessageId, new NewMessageBody($editedText));
$editedMessage = $client->getMessageById($testMessageId);
$testEditPassed = $editedText === $editedMessage->getText();
if (!$testEditPassed) {
    Console::diff($editedText, $editedMessage->getText());
    ManualTestReport::fail(
        'Изменение тестового сообщения',
        'После редактирования текст сообщения не совпал с ожидаемым.',
        notifyUser: true,
    );
}
$deleteInstruction = 'Нажмите «Удалить», чтобы удалить тестовое сообщение и это сообщение.';
$deleteBody = new NewMessageBody($deleteInstruction);
$deleteBody->addInlineKeyboard()->addCallbackButton('Удалить', 'manual:action');
$editEvent->answer($deleteBody);
$expectedControlMessage = [
    'text' => $deleteInstruction,
    'attachments' => [
        [
            'payload' => [
                'buttons' => [
                    [
                        ['payload' => 'manual:action', 'text' => 'Удалить', 'type' => 'callback'],
                    ],
                ],
            ],
            'type' => 'inline_keyboard',
        ],
    ],
];
$updatedControlMessage = $client->getMessageById($controlMessageId);
$actualControlMessage = [
    'text' => $updatedControlMessage->getText(),
    'attachments' => $updatedControlMessage->getBody()->getRawData()['attachments'] ?? [],
];
$controlEditPassed = Console::compareArrays($expectedControlMessage, $actualControlMessage);
if (!$controlEditPassed) {
    ManualTestReport::fail(
        'Изменение сообщения с кнопкой',
        'Текст инструкции или кнопка «Удалить» не совпали с ожидаемыми.',
        notifyUser: true,
    );
}
Max::waitForMessageCallbackEvent($controlMessageId);
$messageIds = [$testMessageId, $controlMessageId];
foreach ($messageIds as $messageId) {
    $client->deleteMessage($messageId);
}
$deletePassed = true;
foreach ($messageIds as $messageId) {
    $deleted = false;
    try {
        $client->getMessageById($messageId);
    } catch (NotFoundException $exception) {
        $deleted = $exception->getResponse()->getHttpCode() === 404;
    }
    if (!$deleted) {
        $deletePassed = false;
        ManualTestReport::fail(
            'Удаление сообщения',
            sprintf('Повторное чтение сообщения %s не вернуло HTTP 404.', $messageId),
            notifyUser: true,
        );
    }
}
if ($testEditPassed && $deletePassed) {
    Console::success('Изменение сообщения и удаление обоих сообщений прошли успешно.');
}

// Проверка кнопки открытия ссылки.
$linkYesPayload = 'sdk-test:' . $runId . ':link:yes';
$linkNoPayload = 'sdk-test:' . $runId . ':link:no';
$linkBody = new NewMessageBody(
    'Нажмите «Открыть ссылку»: должна открыться документация MAX. '
    . 'Затем вернитесь в чат и нажмите «Открылась» или «Не открылась».',
);
$linkBody
    ->addInlineKeyboard()
    ->addLinkButton('Открыть ссылку', 'https://dev.max.ru/')
    ->newRow()
    ->addCallbackButton('Открылась', $linkYesPayload)
    ->addCallbackButton('Не открылась', $linkNoPayload);
$linkMessage = Max::sendMessageToTestUser($linkBody);
$linkValidator = static fn(MessageCallbackEvent $event): bool
    => in_array($event->getCallback()->getPayload(), [$linkYesPayload, $linkNoPayload], true);
$linkEvent = Max::waitForMessageCallbackEvent($linkMessage->getBody()->getMid(), $linkValidator);
if ($linkEvent->getCallback()->getPayload() === $linkYesPayload) {
    $linkEvent->answer('Открытие ссылки подтверждено.');
    Console::success('Кнопка открытия ссылки проверена успешно.');
} else {
    $linkEvent->answer('Проверка открытия ссылки отмечена как непройденная.');
    ManualTestReport::fail('Кнопка открытия ссылки', 'Пользователь отметил проверку как непройденную.');
}
// Проверка кнопки отправки сообщения.
$messageButtonText = 'Тестовое сообщение ' . $runId;
$text = 'Нажмите «' . $messageButtonText . '». Текст отправится автоматически; не вводите его вручную.';
$messageButtonBody = new NewMessageBody($text);
$messageButtonBody->addInlineKeyboard()->addMessageButton($messageButtonText);
Max::sendMessageToTestUser($messageButtonBody);
Max::waitForExactDialogText($messageButtonText);
Console::success('Кнопка отправки сообщения проверена успешно.');
// Проверка кнопки запроса контакта.
$contactBody = new NewMessageBody('Нажмите «Поделиться номером» и подтвердите отправку своего контакта.');
$contactBody->addInlineKeyboard()->addRequestContactButton('Поделиться номером');
Max::sendMessageToTestUser($contactBody);
$contactValidator = static fn(MessageCreatedEvent $event): bool => $event->isContact();
$contactEvent = Max::waitForMessageCreatedEvent($contactValidator);
/** @var ContactAttachment $contact */
$contact = $contactEvent->getContactAttachment();
$expectedContact = ['is_self_contact' => true, 'has_phone' => true];
$actualContact = [
    'is_self_contact' => $contactEvent->isSelfContact(),
    'has_phone' => ($contact->getPayload()->getPhones() ?? []) !== [],
];
if (!Console::compareArrays($expectedContact, $actualContact)) {
    ManualTestReport::fail(
        'Кнопка запроса контакта',
        'Полученный контакт не подтверждён как собственный контакт пользователя с номером телефона.',
        notifyUser: true,
    );
} else {
    Console::success('Кнопка запроса контакта проверена успешно.');
}

// Проверка получения и отправки стороннего контакта с номером телефона.
Max::sendMessageToTestUser(new NewMessageBody('Отправьте боту контакт другого человека.'));
$otherContactValidator = static fn(MessageCreatedEvent $event): bool => $event->isContact();
while (true) {
    $otherContactEvent = Max::waitForMessageCreatedEvent($otherContactValidator);
    /** @var ContactAttachment $contact */
    $contact = $otherContactEvent->getContactAttachment();
    if (
        $otherContactEvent->isSelfContact()
        || $contact->getPayload()->getMaxInfo()?->getUserId() === Max::getTestUserId()
    ) {
        Max::sendMessageToTestUser(new NewMessageBody('Отправьте контакт другого человека, а не свой.'));

        continue;
    }
    if (($contact->getPayload()->getPhones() ?? []) === []) {
        Max::sendMessageToTestUser(new NewMessageBody(
            'В контакте нет номера телефона. Отправьте с телефона контакт другого человека с номером.',
        ));

        continue;
    }

    break;
}
/** @var ContactAttachment $otherContact */
$otherContact = $otherContactEvent->getContactAttachment();
$otherContactBody = new NewMessageBody();
$otherContactBody->addContactAttachment(vcfInfo: $otherContact->getPayload()->getVcfInfo());
Max::sendMessageToTestUser($otherContactBody);
if (Max::isSentMessageValid()) {
    Console::success('Получение и отправка стороннего контакта с номером телефона прошли успешно.');
}

// Проверка кнопки запроса геолокации в обычном и быстром режимах.
foreach ([false, true] as $quick) {
    $locationButtonText = $quick ? 'Быстрая геолокация' : 'Геолокация';
    $locationBody = new NewMessageBody(
        'Нажмите «' . $locationButtonText . '» и отправьте боту свою геолокацию.',
    );
    $locationBody->addInlineKeyboard()->addRequestGeoLocationButton($locationButtonText, $quick);
    Max::sendMessageToTestUser($locationBody);
    $locationValidator = static function (MessageCreatedEvent $event): bool {
        $attachments = $event->getMessage()->getBody()->getAttachments();

        return $attachments !== null && count($attachments) === 1 && $attachments[0] instanceof LocationAttachment;
    };
    $locationEvent = Max::waitForMessageCreatedEvent($locationValidator);
    /** @var LocationAttachment $location */
    $location = $locationEvent->getMessage()->getBody()->getAttachments()[0] ?? null;
    $expectedLocation = ['latitude_in_range' => true, 'longitude_in_range' => true];
    $actualLocation = [
        'latitude_in_range' => $location->getLatitude() >= -90 && $location->getLatitude() <= 90,
        'longitude_in_range' => $location->getLongitude() >= -180 && $location->getLongitude() <= 180,
    ];
    if (!Console::compareArrays($expectedLocation, $actualLocation)) {
        ManualTestReport::fail(
            $locationButtonText,
            'Получены координаты вне допустимого диапазона.',
            notifyUser: true,
        );
    } else {
        Console::success('Кнопка «' . $locationButtonText . '» проверена успешно.');
        if ($quick) {
            $replyLocationBody = new NewMessageBody();
            $replyLocationBody->addLocationAttachment($location->getLatitude(), $location->getLongitude());
            Max::sendMessageToTestUser($replyLocationBody);
            if (Max::isSentMessageValid()) {
                Console::success('Отправка полученной геолокации прошла успешно.');
            }
        }
    }
}

// Проверка отправки ссылки с вложением Share.
$shareUrl = 'https://dev.max.ru/';
$shareText = 'Документация MAX: ' . $shareUrl;
$shareMarkup = [['from' => 18, 'length' => 19, 'type' => 'link', 'url' => $shareUrl]];
$shareBody = new NewMessageBody($shareText);
$shareBody->addShareAttachment($shareUrl);
Max::sendFormattedTextToTestUser($shareBody, $shareText, $shareMarkup);
if (Max::isSentMessageValid()) {
    Console::success('Отправка ссылки с вложением Share прошла успешно.');
}

// Проверка автоматического добавления Share для ссылки в тексте сообщения.
$linkPreviewBody = new NewMessageBody($shareText);
$linkPreviewMessage = Max::sendFormattedTextToTestUser($linkPreviewBody, $shareText, $shareMarkup);
$storedLinkMessage = $client->getMessageById($linkPreviewMessage->getBody()->getMid());
$actualShareUrls = [];
foreach ($storedLinkMessage->getBody()->getAttachments() ?? [] as $attachment) {
    if ($attachment instanceof ShareAttachment) {
        $actualShareUrls[] = $attachment->getPayload()->getUrl();
    }
}
$expectedShareUrls = [$shareUrl];
if (!Console::compareArrays($expectedShareUrls, $actualShareUrls)) {
    ManualTestReport::fail(
        'Автоматическое добавление вложения Share',
        'После getMessageById в сообщении нет ожидаемого вложения Share с отправленной ссылкой.',
        notifyUser: true,
    );
} else {
    Console::success('Вложение Share подтверждено через getMessageById.');
}

// Проверка запрета добавления Share для ссылки в тексте сообщения.
$noPreviewUrl = 'https://dev.max.ru/';
$noPreviewText = 'Документация MAX: ' . $noPreviewUrl;
$noPreviewMessage = Max::sendMessageToTestUser(
    new NewMessageBody($noPreviewText),
    disableLinkPreview: true,
);
$storedNoPreviewMessage = $client->getMessageById($noPreviewMessage->getBody()->getMid());
$unexpectedShares = [];
foreach ($storedNoPreviewMessage->getBody()->getAttachments() ?? [] as $attachment) {
    if ($attachment instanceof ShareAttachment) {
        $unexpectedShares[] = $attachment->getRawData();
    }
}
if (!Console::compareArrays([], $unexpectedShares)) {
    ManualTestReport::fail(
        'Запрет добавления вложения Share',
        'После getMessageById найдено вложение Share при disableLinkPreview: true.',
        notifyUser: true,
    );
} else {
    Console::success('Отсутствие Share при запрете предпросмотра подтверждено через getMessageById.');
}

// Получение изображения, видео и файла для отправки пользователю.
$mediaChecks = [
    [AttachmentType::Image, 'Отправьте боту одно изображение без подписи.'],
    [AttachmentType::Video, 'Отправьте боту одно видео без подписи.'],
    [AttachmentType::File, 'Отправьте боту один файл без подписи.'],
];
$receivedMedia = [];
foreach ($mediaChecks as [$type, $instruction]) {
    Max::sendMessageToTestUser(new NewMessageBody($instruction));
    $mediaValidator = static function (MessageCreatedEvent $event) use ($type): bool {
        $attachments = $event->getMessage()->getBody()->getAttachments();

        return $attachments !== null && count($attachments) === 1 && $attachments[0]->getType() === $type;
    };
    $mediaEvent = Max::waitForMessageCreatedEvent($mediaValidator);
    /** @var PhotoAttachment|VideoAttachment|FileAttachment $media */
    $media = $mediaEvent->getMessage()->getBody()->getAttachments()[0] ?? null;
    $receivedMedia[$type->value] = $media;
    Console::success('Получено вложение ' . $type->value . '.');
}

// Проверка отправки полученного файла.
$fileBody = new NewMessageBody();
$fileBody->addFileAttachment($receivedMedia[AttachmentType::File->value]->getPayload()->getToken());
Max::sendMessageToTestUser($fileBody);
if (Max::isSentMessageValid()) {
    Console::success('Отправка полученного файла прошла успешно.');
}

// Проверка отправки полученных изображения и видео одним сообщением.
$albumBody = new NewMessageBody();
$albumBody->addImageAttachment($receivedMedia[AttachmentType::Image->value]->getPayload()->getToken());
$albumBody->addVideoAttachment($receivedMedia[AttachmentType::Video->value]->getPayload()->getToken());
Max::sendMessageToTestUser($albumBody);
if (Max::isSentMessageValid()) {
    Console::success('Отправка изображения и видео одним сообщением прошла успешно.');
}

$heading = 'Этап 1 завершён';
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
