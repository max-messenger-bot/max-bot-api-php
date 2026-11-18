<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Manual\Support;

use LogicException;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAddedToChatEvent;
use MaxMessenger\Bot\MaxBot\Event\BotAdminPermissionsChangedEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\ChatType;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Request\AudioAttachmentRequest;
use MaxMessenger\Bot\Model\Request\FileAttachmentRequest;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\PhotoAttachmentRequest;
use MaxMessenger\Bot\Model\Request\ShareAttachmentRequest;
use MaxMessenger\Bot\Model\Request\StickerAttachmentRequest;
use MaxMessenger\Bot\Model\Request\VideoAttachmentRequest;
use MaxMessenger\Bot\Model\Response\AudioAttachment;
use MaxMessenger\Bot\Model\Response\BotInfo;
use MaxMessenger\Bot\Model\Response\FileAttachment;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\PhotoAttachment;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\ShareAttachment;
use MaxMessenger\Bot\Model\Response\StickerAttachment;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\User;
use MaxMessenger\Bot\Model\Response\VideoAttachment;
use RuntimeException;
use Throwable;

use function array_keys;
use function in_array;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function ksort;
use function min;
use function sprintf;
use function strlen;
use function time;
use function trim;

/**
 * Вспомогательные действия с MAX для ручных сценариев.
 */
final class Max
{
    private static ?MaxApiClient $apiClient = null;
    private static ?int $marker = null;
    private static ?BotInfo $myInfo = null;
    private static ?bool $sentMessageValid = null;
    private static ?int $testChatId = null;
    private static ?int $testDialogId = null;
    private static ?User $testUser = null;
    private static ?int $testUserId = null;

    /**
     * Создаёт один API-клиент для ручных сценариев.
     *
     * @return MaxApiClient API-клиент текущего запуска.
     */
    public static function apiClient(): MaxApiClient
    {
        if (self::$apiClient === null) {
            self::initializeApiClient();
        }

        return self::$apiClient ?? throw new RuntimeException('Не удалось создать API-клиент.');
    }

    /**
     * Задаёт вопрос с кнопками «Да» и «Нет» в существующем сообщении.
     *
     * @param non-empty-string $messageId ID сообщения.
     * @param string $question Текст вопроса.
     * @return bool Ответ пользователя: true для «Да», false для «Нет».
     */
    public static function askInExistingMessage(string $messageId, string $question): bool
    {
        $yes = 'manual:yes';
        $no = 'manual:no';
        $body = new NewMessageBody($question);
        $body->addInlineKeyboard()->addCallbackButton('✅ Да', $yes)->addCallbackButton('❌ Нет', $no);
        self::apiClient()->editMessage($messageId, $body);
        $validator = static fn(MessageCallbackEvent $event): bool
            => in_array($event->getCallback()->getPayload(), [$yes, $no], true);
        $event = self::waitForMessageCallbackEvent($messageId, $validator);

        return $event->getCallback()->getPayload() === $yes;
    }

    /**
     * Задаёт вопрос с кнопками «Да» и «Нет» в тестовом чате или личном диалоге.
     *
     * @param string $question Текст вопроса.
     * @param bool $dialog Отправить вопрос в личный диалог.
     * @return bool Ответ пользователя: true для «Да», false для «Нет».
     */
    public static function askInTestChat(string $question, bool $dialog = false): bool
    {
        $yes = 'manual:yes';
        $no = 'manual:no';
        $body = new NewMessageBody($question);
        $body->addInlineKeyboard()->addCallbackButton('✅ Да', $yes)->addCallbackButton('❌ Нет', $no);
        $message = $dialog ? self::sendMessageToTestUser($body) : self::sendMessageToTestChat($body);
        $validator = static fn(MessageCallbackEvent $event): bool
            => in_array($event->getCallback()->getPayload(), [$yes, $no], true);
        $event = self::waitForMessageCallbackEvent($message->getBody()->getMid(), $validator);
        $confirmed = $event->getCallback()->getPayload() === $yes;
        $event->answer($confirmed ? 'Проверка подтверждена.' : 'Проверка отмечена как непройденная.');

        return $confirmed;
    }

    /**
     * Задаёт вопрос с кнопкой действия и кнопками «Да» и «Нет» в тестовом чате.
     *
     * @param string $question Текст вопроса.
     * @param non-empty-string $actionText Текст кнопки действия.
     * @param callable(): void $action Действие при нажатии верхней кнопки.
     * @return bool Ответ пользователя: true для «Да», false для «Нет».
     */
    public static function askInTestChatWithAction(string $question, string $actionText, callable $action): bool
    {
        $yes = 'manual:yes';
        $no = 'manual:no';
        $actionPayload = 'manual:action';
        $body = new NewMessageBody($question);
        $body
            ->addInlineKeyboard()
            ->addCallbackButton($actionText, $actionPayload)
            ->newRow()
            ->addCallbackButton('✅ Да', $yes)
            ->addCallbackButton('❌ Нет', $no);
        $message = self::sendMessageToTestChat($body);
        $messageId = $message->getBody()->getMid();
        $validator = static fn(MessageCallbackEvent $event): bool
            => in_array($event->getCallback()->getPayload(), [$actionPayload, $yes, $no], true);
        do {
            $event = self::waitForMessageCallbackEvent($messageId, $validator);
            $selected = $event->getCallback()->getPayload();
            if ($selected === $actionPayload) {
                $action();
            }
        } while ($selected === $actionPayload);
        $confirmed = $selected === $yes;
        $event->answer($confirmed ? 'Проверка подтверждена.' : 'Проверка отмечена как непройденная.');

        return $confirmed;
    }

    /**
     * @return BotInfo Информация о боте, загружаемая при первом обращении.
     */
    public static function getMyInfo(): BotInfo
    {
        return self::$myInfo ??= self::apiClient()->getMyInfo();
    }

    /**
     * @return int ID чата текущего теста.
     */
    public static function getTestChatId(): int
    {
        return self::$testChatId ?? throw new LogicException('Не определён ID чата теста.');
    }

    /**
     * @return int ID личного диалога с пользователем теста.
     */
    public static function getTestDialogId(): int
    {
        return self::$testDialogId ?? throw new LogicException('Не определён ID личного диалога.');
    }

    /**
     * @return User Пользователь текущего теста.
     */
    public static function getTestUser(): User
    {
        return self::$testUser ?? throw new LogicException('Не определён пользователь теста.');
    }

    /**
     * @return int ID пользователя теста.
     */
    public static function getTestUserId(): int
    {
        return self::$testUserId ?? throw new LogicException('Не определён пользователь теста.');
    }

    /**
     * Проверяет, определён ли пользователь теста.
     */
    public static function hasTestUser(): bool
    {
        return self::$testUserId !== null;
    }

    /**
     * Определяет пользователя теста по первому сообщению и сохраняет ID личного диалога.
     *
     * @param MessageCreatedEvent $event Первое сообщение пользователя.
     */
    public static function identifyTestUser(MessageCreatedEvent $event): void
    {
        $message = $event->getMessage();
        $user = $message->getSender();
        if ($user === null) {
            throw new LogicException('Не удалось определить отправителя сообщения.');
        }

        self::$testUserId = $user->getUserId();
        self::$testDialogId = $message->getRecipient()->getChatId();
        self::$testUser = $user;

        Console::text('Получено сообщение от %s (ID %d).', $user->getFullName(), self::$testUserId);
    }

    /**
     * Отправляет инструкцию в личный диалог и выводит её в консоль.
     *
     * @param string $message Текст или шаблон инструкции.
     * @param string|int|float ...$arguments Аргументы шаблона.
     */
    public static function instruction(string $message, string|int|float ...$arguments): void
    {
        self::sendMessageToTestUser(new NewMessageBody(
            $arguments === [] ? $message : sprintf($message, ...$arguments),
        ));
        Console::instruction($message, ...$arguments);
    }

    /**
     * Отправляет инструкцию в тестовый чат и выводит её в консоль.
     *
     * @param string $message Текст или шаблон инструкции.
     * @param string|int|float ...$arguments Аргументы шаблона.
     */
    public static function instructionToChat(string $message, string|int|float ...$arguments): void
    {
        self::sendMessageToTestChat(new NewMessageBody(
            $arguments === [] ? $message : sprintf($message, ...$arguments),
        ));
        Console::instruction($message, ...$arguments);
    }

    /**
     * @return bool Успешна ли последняя проверка отправленного сообщения.
     */
    public static function isSentMessageValid(): bool
    {
        return self::$sentMessageValid ?? throw new LogicException('Отправленное сообщение ещё не проверялось.');
    }

    /**
     * Отправляет форматированный текст в чат текущего теста.
     *
     * @param NewMessageBody $message Тело отправляемого сообщения.
     * @param string $text Ожидаемый текст после форматирования.
     * @param list<array<string, int|string>> $markup Ожидаемая разметка текста.
     * @param bool $disableLinkPreview Запретить автоматическое добавление предпросмотра ссылки.
     */
    public static function sendFormattedTextToTestChat(
        NewMessageBody $message,
        string $text,
        array $markup,
        bool $disableLinkPreview = false,
    ): void {
        $result = self::apiClient()->sendMessageToChat(self::getTestChatId(), $message, $disableLinkPreview);

        self::checkSentMessage($message, $result, [
            'chat_type' => ChatType::Chat->value,
            'chat_id' => self::getTestChatId(),
        ], ['text' => $text, 'markup' => $markup]);
    }

    /**
     * Отправляет форматированный текст текущему пользователю теста.
     *
     * @param NewMessageBody $message Тело отправляемого сообщения.
     * @param string $text Ожидаемый текст после форматирования.
     * @param list<array<string, int|string>> $markup Ожидаемая разметка текста.
     * @param bool $disableLinkPreview Запретить автоматическое добавление предпросмотра ссылки.
     * @return Message Отправленное сообщение.
     */
    public static function sendFormattedTextToTestUser(
        NewMessageBody $message,
        string $text,
        array $markup,
        bool $disableLinkPreview = false,
    ): Message {
        $result = self::apiClient()->sendMessageToUser(self::getTestUserId(), $message, $disableLinkPreview);
        if (self::$testDialogId === null) {
            $recipient = $result->getMessage()->getRecipient();
            if ($recipient->getUserId() === self::getTestUserId()) {
                self::$testDialogId = $recipient->getChatId();
            }
        }

        self::checkSentMessage($message, $result, [
            'chat_type' => ChatType::Dialog->value,
            'chat_id' => self::getTestDialogId(),
            'user_id' => self::getTestUserId(),
        ], ['text' => $text, 'markup' => $markup]);

        return $result->getMessage();
    }

    /**
     * Отправляет новое сообщение в канал.
     *
     * @param int $channelId ID канала.
     * @param NewMessageBody $message Тело отправляемого сообщения.
     * @param bool $disableLinkPreview Запретить автоматическое добавление предпросмотра ссылки.
     * @return Message Отправленное сообщение.
     */
    public static function sendMessageToChannel(
        int $channelId,
        NewMessageBody $message,
        bool $disableLinkPreview = false,
    ): Message {
        $result = self::apiClient()->sendMessageToChat($channelId, $message, $disableLinkPreview);

        self::checkSentMessage($message, $result, [
            'chat_type' => ChatType::Channel->value,
            'chat_id' => $channelId,
        ], ['text' => $message->getText() ?? '']);

        return $result->getMessage();
    }

    /**
     * Отправляет сообщение с кнопкой в канал и ожидает её нажатия.
     *
     * @param int $channelId ID канала.
     * @param string $text Текст сообщения.
     * @param non-empty-string $buttonText Текст кнопки.
     * @return non-empty-string ID отправленного сообщения.
     */
    public static function sendMessageToChannelAndWaitForCallback(
        int $channelId,
        string $text,
        string $buttonText,
    ): string {
        $body = new NewMessageBody($text);
        $body->addInlineKeyboard()->addCallbackButton($buttonText, 'manual:action');
        $messageId = self::sendMessageToChannel($channelId, $body)->getBody()->getMid();
        self::waitForMessageCallbackEvent($messageId);

        return $messageId;
    }

    /**
     * Отправляет новое сообщение в чат текущего теста.
     *
     * @param NewMessageBody $message Тело отправляемого сообщения.
     * @param bool $disableLinkPreview Запретить автоматическое добавление предпросмотра ссылки.
     * @return Message Отправленное сообщение.
     */
    public static function sendMessageToTestChat(
        NewMessageBody $message,
        bool $disableLinkPreview = false,
    ): Message {
        $result = self::apiClient()->sendMessageToChat(self::getTestChatId(), $message, $disableLinkPreview);

        self::checkSentMessage($message, $result, [
            'chat_type' => ChatType::Chat->value,
            'chat_id' => self::getTestChatId(),
        ], ['text' => $message->getText() ?? '']);

        return $result->getMessage();
    }

    /**
     * Отправляет новое сообщение текущему пользователю теста.
     *
     * @param NewMessageBody $message Тело отправляемого сообщения.
     * @param bool $disableLinkPreview Запретить автоматическое добавление предпросмотра ссылки.
     * @return Message Отправленное сообщение.
     */
    public static function sendMessageToTestUser(
        NewMessageBody $message,
        bool $disableLinkPreview = false,
    ): Message {
        $result = self::apiClient()->sendMessageToUser(self::getTestUserId(), $message, $disableLinkPreview);
        if (self::$testDialogId === null) {
            $recipient = $result->getMessage()->getRecipient();
            if ($recipient->getUserId() === self::getTestUserId()) {
                self::$testDialogId = $recipient->getChatId();
            }
        }

        self::checkSentMessage($message, $result, [
            'chat_type' => ChatType::Dialog->value,
            'chat_id' => self::getTestDialogId(),
            'user_id' => self::getTestUserId(),
        ], ['text' => $message->getText() ?? '']);

        return $result->getMessage();
    }

    /**
     * Сохраняет ID чата текущего теста.
     *
     * @param int $chatId ID чата теста.
     */
    public static function setTestChatId(int $chatId): void
    {
        self::$testChatId = $chatId;
    }

    /**
     * Сохраняет пользователя текущего теста.
     *
     * @param User $user Пользователь теста.
     */
    public static function setTestUser(User $user): void
    {
        if (self::$testUserId !== $user->getUserId()) {
            self::$testDialogId = null;
        }
        self::$testUser = $user;
        self::$testUserId = $user->getUserId();
    }

    /**
     * Определяет тестовый групповой чат по сообщению пользователя, если чат ещё не задан.
     */
    public static function startChatTest(): void
    {
        if (self::$testChatId !== null) {
            return;
        }
        $text = 'sdk-chat-test-' . ManualEnvironment::getTestRunId();
        $instruction = 'В тестовом групповом чате назначьте бота администратором с правом «'
            . ChatPermissions::label(ChatAdminPermission::ReadAllMessages) . '» '
            . 'и отправьте точный текст: «%s».';
        Console::instruction($instruction, $text);
        $validator = static function (BaseEvent $event) use ($text): bool {
            if ($event instanceof BotAddedToChatEvent || $event instanceof BotAdminPermissionsChangedEvent) {
                return true;
            }
            if (!$event instanceof MessageCreatedEvent) {
                return false;
            }
            $message = $event->getMessage();

            return $message->getRecipient()->getChatType() === ChatType::Chat
                && $message->getSender()?->isBot() === false
                && $message->getText() === $text;
        };
        do {
            $event = self::waitForEvent(
                [UpdateType::BotAdded, UpdateType::BotAdminPermissionsChanged, UpdateType::MessageCreated],
                $validator,
                userId: null,
            );
            if ($event instanceof BotAddedToChatEvent || $event instanceof BotAdminPermissionsChangedEvent) {
                Console::text(
                    'Зафиксировано событие %s в чате ID %d.',
                    $event->update->getUpdateTypeRaw(),
                    $event->getChatId(),
                );
                if (!$event->isChannel()) {
                    $membership = self::apiClient()->getMembership($event->getChatId());
                    if (!$membership->isAdmin() || !$membership->hasPermission(ChatAdminPermission::ReadAllMessages)) {
                        $body = new NewMessageBody(
                            'Назначьте бота администратором с правом «'
                            . ChatPermissions::label(ChatAdminPermission::ReadAllMessages) . '».',
                        );
                    } else {
                        $body = new NewMessageBody('Нажмите кнопку, чтобы начать тест.');
                        $body->addInlineKeyboard()->addMessageButton($text);
                    }
                    self::apiClient()->sendMessageToChat($event->getChatId(), $body);
                }
            }
        } while (!$event instanceof MessageCreatedEvent);
        $message = $event->getMessage();
        $user = $message->getSender() ?? throw new LogicException('Не удалось определить отправителя сообщения.');
        self::setTestUser($user);
        self::$testChatId = $message->getRecipient()->getChatId();
        Console::text('Тестовый чат: %d.', self::$testChatId);
    }

    /**
     * Начинает тест личного диалога и определяет пользователя, если он ещё не определён.
     */
    public static function startDialogTest(): void
    {
        if (self::$testUser !== null) {
            return;
        }
        $bot = self::getMyInfo();
        $startText = '/sdk-test-' . ManualEnvironment::getTestRunId();
        Console::text('Бот: %s (ID %d)', $bot->getFullName(), $bot->getUserId());
        $username = $bot->getUsername();
        $botName = $username !== null ? '@' . $username : $bot->getFullName();
        Console::instruction('Отправьте боту %s в личный диалог точный текст: «%s»', $botName, $startText);

        self::identifyTestUser(self::waitForExactDialogText($startText, userId: null));
    }

    /**
     * Ожидает событие нужного типа, которое примет валидатор, и обновляет позицию опроса.
     *
     * @template T of BaseEvent
     * @param list<UpdateType>|null $expectedTypes Ожидаемые типы событий; null означает любой тип.
     * @param callable(T): bool $validator Возвращает true для нужного события, иначе событие пропускается.
     * @param int|true|null $userId ID пользователя; true означает пользователя теста, null отключает проверку.
     * @param positive-int $waitSeconds Общее время ожидания в секундах.
     * @return T Принятое событие.
     */
    public static function waitForEvent(
        ?array $expectedTypes,
        callable $validator,
        int|true|null $userId = true,
        int $waitSeconds = 600,
    ): BaseEvent {
        $client = self::apiClient();
        $userId = $userId === true ? self::getTestUserId() : $userId;
        $deadline = time() + $waitSeconds;

        while (($remainingSeconds = $deadline - time()) > 0) {
            $response = $client->getUpdates(
                limit: 1,
                timeout: min(30, $remainingSeconds),
                marker: self::$marker,
            );
            self::$marker = $response->getMarker() ?? self::$marker;

            foreach ($response->getUpdates() as $update) {
                if ($expectedTypes !== null && !in_array($update->getUpdateType(), $expectedTypes, true)) {
                    self::printSkippedUpdate($update, 'тип события не входит в список ожидаемых');

                    continue;
                }
                if (!$update->isValid()) {
                    self::printSkippedUpdate($update, 'в обновлении отсутствуют обязательные поля');

                    continue;
                }
                /** @var T $event */
                $event = BaseEvent::new($update, $client, []);
                if ($userId !== null && $event->getUserId() !== $userId) {
                    self::printSkippedUpdate($update, 'событие относится к другому пользователю');

                    continue;
                }
                if ($validator($event)) {
                    return $event;
                }
                self::printSkippedUpdate($update, 'событие отклонено валидатором');
            }
        }

        throw new RuntimeException(sprintf('За %d секунд ожидаемое событие не получено.', $waitSeconds));
    }

    /**
     * Ожидает точный текст от человека в личном диалоге с ботом.
     *
     * @param non-empty-string $expectedText Ожидаемый текст сообщения.
     * @param int|true|null $userId ID отправителя; true означает пользователя теста, null отключает проверку.
     * @return MessageCreatedEvent Найденное событие с ожидаемым текстом.
     */
    public static function waitForExactDialogText(
        string $expectedText,
        int|true|null $userId = true,
    ): MessageCreatedEvent {
        $validator = static function (MessageCreatedEvent $event) use ($expectedText): bool {
            $message = $event->getMessage();
            if ($message->getSender()?->isBot() !== false) {
                return false;
            }

            return $message->getText() === $expectedText;
        };

        return self::waitForMessageCreatedEvent($validator, userId: $userId);
    }

    /**
     * Ожидает событие нажатия callback-кнопки указанного сообщения.
     *
     * @param non-empty-string $messageId ID сообщения с ожидаемой callback-кнопкой.
     * @param callable(MessageCallbackEvent): bool|null $validator Дополнительная проверка события.
     * @param int|true|null $userId ID пользователя; true означает пользователя теста, null отключает проверку.
     * @param positive-int $waitSeconds Общее время ожидания в секундах.
     * @return MessageCallbackEvent Принятое событие нажатия кнопки.
     */
    public static function waitForMessageCallbackEvent(
        string $messageId,
        ?callable $validator = null,
        int|true|null $userId = true,
        int $waitSeconds = 600,
    ): MessageCallbackEvent {
        $matches = static fn(MessageCallbackEvent $event): bool
            => $event->getMessage()->getBody()->getMid() === $messageId
            && ($validator === null || $validator($event));

        return self::waitForEvent([UpdateType::MessageCallback], $matches, $userId, $waitSeconds);
    }

    /**
     * Ожидает событие создания сообщения, которое примет валидатор.
     *
     * @param callable(MessageCreatedEvent): bool $validator Проверяет событие с доступным сообщением.
     * @param ChatType|null $chatType Тип чата; null отключает проверку.
     * @param int|true|null $userId ID отправителя; true означает пользователя теста, null отключает проверку.
     * @param positive-int $waitSeconds Общее время ожидания в секундах.
     * @return MessageCreatedEvent Принятое событие с сообщением.
     */
    public static function waitForMessageCreatedEvent(
        callable $validator,
        ?ChatType $chatType = ChatType::Dialog,
        int|true|null $userId = true,
        int $waitSeconds = 600,
    ): MessageCreatedEvent {
        $matches = static function (MessageCreatedEvent $event) use ($validator, $chatType): bool {
            $message = $event->getMessage();

            return ($chatType === null || $message->getRecipient()->getChatType() === $chatType)
                && $validator($event);
        };

        return self::waitForEvent([UpdateType::MessageCreated], $matches, $userId, $waitSeconds);
    }

    /**
     * @param array<string, mixed> $expectedRecipient
     * @param array<string, mixed> $expectedBody
     */
    private static function checkSentMessage(
        NewMessageBody $message,
        SendMessageResult $result,
        array $expectedRecipient,
        array $expectedBody,
    ): void {
        $sentMessage = $result->getMessage();
        $bot = self::getMyInfo();
        $expectedSender = [
            'user_id' => $bot->getUserId(),
            'first_name' => $bot->getFirstName(),
            'is_bot' => true,
        ];
        if ($bot->getUsername() !== null) {
            $expectedSender['username'] = $bot->getUsername();
        }
        $lastActivityTime = $sentMessage->getSender()?->getLastActivityTimeRaw();
        if ($lastActivityTime !== null) {
            $expectedSender['last_activity_time'] = $lastActivityTime;
        }
        $expectedSender['name'] = $bot->getFullName();

        $body = $sentMessage->getBody();
        $requestAttachments = $message->getAttachments();
        if ($requestAttachments !== null) {
            $responseAttachments = $body->getAttachments() ?? [];
            $expectedAttachments = [];
            foreach ($requestAttachments as $index => $attachment) {
                /** @var array<string, mixed> $expectedAttachment */
                $expectedAttachment = json_decode(
                    json_encode($attachment, JSON_THROW_ON_ERROR),
                    true,
                    flags: JSON_THROW_ON_ERROR,
                );
                $responseAttachment = $responseAttachments[$index] ?? null;
                if (
                    ($attachment instanceof PhotoAttachmentRequest && $responseAttachment instanceof PhotoAttachment)
                    || ($attachment instanceof VideoAttachmentRequest && $responseAttachment instanceof VideoAttachment)
                    || ($attachment instanceof AudioAttachmentRequest && $responseAttachment instanceof AudioAttachment)
                    || ($attachment instanceof FileAttachmentRequest && $responseAttachment instanceof FileAttachment)
                ) {
                    $expectedAttachment = self::mediaAttachmentTemplate($responseAttachment);
                }
                if (
                    $attachment instanceof ShareAttachmentRequest
                    && $responseAttachment instanceof ShareAttachment
                ) {
                    $expectedPayload = ['url' => $attachment->getPayload()->getUrl()];
                    $token = $attachment->getPayload()->getToken() ?? $responseAttachment->getPayload()->getToken();
                    if ($token !== null) {
                        $expectedPayload['token'] = $token;
                    }
                    $expectedAttachment['payload'] = $expectedPayload;
                    $preview = [
                        'title' => $responseAttachment->getTitle(),
                        'description' => $responseAttachment->getDescription(),
                        'image_url' => $responseAttachment->getImageUrl(),
                    ];
                    foreach ($preview as $key => $value) {
                        if ($value !== null) {
                            $expectedAttachment[$key] = $value;
                        }
                    }
                }
                if (
                    $attachment instanceof StickerAttachmentRequest
                    && $responseAttachment instanceof StickerAttachment
                ) {
                    $expectedAttachment['width'] = $responseAttachment->getWidth();
                    $expectedAttachment['height'] = $responseAttachment->getHeight();
                    $expectedAttachment['payload'] = [
                        'code' => $attachment->getPayload()->getCode(),
                        'url' => $responseAttachment->getPayload()->getUrl(),
                    ];
                }
                $expectedAttachments[] = $expectedAttachment;
            }
            $expectedBody['attachments'] = $expectedAttachments;
        }
        $expectedBody = ['mid' => $body->getMid(), 'seq' => $body->getSeq(), ...$expectedBody];

        $expected = [
            'message' => [
                'recipient' => $expectedRecipient,
                'timestamp' => $sentMessage->getTimestampRaw(),
                'body' => $expectedBody,
            ],
        ];
        if ($expectedRecipient['chat_type'] === ChatType::Channel->value) {
            $stat = $sentMessage->getStat();
            if ($stat !== null) {
                $expected['message']['stat'] = ['views' => $stat->getViews()];
            }
            $url = $sentMessage->getUrl();
            if ($url !== null) {
                $expected['message']['url'] = $url;
            }
        } else {
            $expected['message']['sender'] = $expectedSender;
        }
        $expected = self::sortedRawData($expected);
        $actual = self::sortedRawData($result->getRawData());
        self::$sentMessageValid = $expected === $actual;
        if (self::$sentMessageValid) {
            return;
        }

        Console::text(
            "Отправленное сообщение (формат: %s):\n%s",
            $message->getFormat()?->value ?? 'обычный текст',
            $message->getText() ?? '',
        );
        Console::diff($expected, $actual);

        ManualTestReport::fail(
            sprintf('Отправка сообщения (%s)', $message->getFormat()?->value ?? 'обычный текст'),
            'Ответ не совпал с ожидаемым. Различия выведены в консоль.',
            notifyUser: true,
        );
    }

    private static function initializeApiClient(): void
    {
        $console = Console::console();
        $console->section('Токен доступа');
        $token = Console::selectAccessTokenFromFile();
        if ($token !== null) {
            if (self::tryCreateApiClient($token)) {
                return;
            }
            $console->warning('Выбранный токен не прошёл проверку.');
        }

        while (true) {
            /** @var string|null $answer */
            $answer = $console->ask('Токен доступа Max Bot');
            $token = is_string($answer) ? trim($answer) : '';
            if ($token === '' || strlen($token) < 10) {
                $console->error('Токен доступа должен содержать не менее 10 символов.');

                continue;
            }
            if (self::tryCreateApiClient($token)) {
                return;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function mediaAttachmentTemplate(
        PhotoAttachment|VideoAttachment|AudioAttachment|FileAttachment $attachment,
    ): array {
        $payload = $attachment->getPayload();
        $expected = [
            'type' => $attachment->getTypeRaw(),
            'payload' => [
                'url' => $payload->getUrl(),
                'token' => $payload->getToken(),
            ],
        ];
        if ($attachment instanceof PhotoAttachment) {
            $expected['payload']['photo_id'] = $attachment->getPayload()->getPhotoId();
        } elseif ($attachment instanceof FileAttachment) {
            $expected['payload']['fileId'] = $attachment->getPayload()->getFileId();
            $expected['filename'] = $attachment->getFilename();
            $expected['size'] = $attachment->getSize();
        } else {
            $expected['payload']['id'] = $attachment->getPayload()->getId();
        }
        if ($attachment instanceof VideoAttachment) {
            $metadata = [
                'width' => $attachment->getWidth(),
                'height' => $attachment->getHeight(),
                'duration' => $attachment->getDuration(),
            ];
            foreach ($metadata as $key => $value) {
                if ($value !== null) {
                    $expected[$key] = $value;
                }
            }
            $thumbnail = $attachment->getThumbnail();
            if ($thumbnail !== null) {
                $expected['thumbnail'] = is_string($attachment->getRawData()['thumbnail'] ?? null)
                    ? $thumbnail->getUrl()
                    : ['url' => $thumbnail->getUrl()];
            }
        }

        return $expected;
    }

    private static function printSkippedUpdate(Update $update, string $reason): void
    {
        Console::text(
            'Пропущено событие %s: %s.',
            $update->getUpdateTypeRaw(),
            $reason,
        );
        Console::json($update->getRawData());
    }

    /**
     * @param array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    private static function sortedRawData(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (is_array($data[$key])) {
                $data[$key] = self::sortedRawData($data[$key]);
            }
        }
        ksort($data);

        return $data;
    }

    /**
     * @param non-empty-string $accessToken
     */
    private static function tryCreateApiClient(string $accessToken): bool
    {
        $console = Console::console();

        try {
            $config = new MaxApiConfig($accessToken);
            // Проверка TLS намеренно отключена в ручных тестах для запуска в локальном окружении.
            $config->setVerifySslCertificate(false);
            $client = new MaxApiClient($config);
            if ($client->getSubscriptions()->getSubscriptions() !== []) {
                throw new RuntimeException('Для Long Polling у бота не должно быть Webhook-подписок.');
            }

            self::$apiClient = $client;
            $console->success('Токен доступа проверен.');

            return true;
        } catch (Throwable $exception) {
            $console->error('Токен не прошёл проверку: ' . $exception->getMessage());

            return false;
        }
    }
}
