<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use BackedEnum;
use Closure;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use MaxMessenger\Bot\Exception\RequiredArgumentsException;
use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Enum\UploadType;
use MaxMessenger\Bot\Model\Request\ActionRequestBody;
use MaxMessenger\Bot\Model\Request\BotPatch;
use MaxMessenger\Bot\Model\Request\CallbackAnswer;
use MaxMessenger\Bot\Model\Request\ChatAdmin;
use MaxMessenger\Bot\Model\Request\ChatAdminsList;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\PinMessageBody;
use MaxMessenger\Bot\Model\Request\RawModel;
use MaxMessenger\Bot\Model\Request\SubscriptionRequestBody;
use MaxMessenger\Bot\Model\Request\UserIdsList;
use MaxMessenger\Bot\Model\Request\ValidateTrait;
use MaxMessenger\Bot\Model\Response\BotInfo;
use MaxMessenger\Bot\Model\Response\Chat;
use MaxMessenger\Bot\Model\Response\ChatList;
use MaxMessenger\Bot\Model\Response\ChatMember;
use MaxMessenger\Bot\Model\Response\ChatMembersList;
use MaxMessenger\Bot\Model\Response\ContactAttachmentPayload;
use MaxMessenger\Bot\Model\Response\GetPinnedMessageResult;
use MaxMessenger\Bot\Model\Response\GetSubscriptionsResult;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageList;
use MaxMessenger\Bot\Model\Response\ModifyMembersResult;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\SimpleQueryResult;
use MaxMessenger\Bot\Model\Response\UpdateList;
use MaxMessenger\Bot\Model\Response\UploadEndpoint;
use MaxMessenger\Bot\Model\Response\VideoAttachmentDetails;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientException;
use SensitiveParameter;

use function is_array;
use function is_string;

/**
 * API Max клиент.
 */
final class MaxApiClient
{
    use ValidateTrait;

    /**
     * @var list<positive-int>|null Time before retry in milliseconds. `null` to use the value from the configuration.
     */
    public ?array $retryAttempts = null;
    private readonly MaxApiConfigInterface $config;
    private readonly MaxHttpClientInterface $httpClient;

    /**
     * @param non-empty-string|MaxApiConfigInterface $accessTokenOrConfig
     * @param Closure(non-empty-string $method, HttpClientException $exception): void|null $exceptionLogger
     */
    public function __construct(
        #[SensitiveParameter]
        string|MaxApiConfigInterface $accessTokenOrConfig,
        private readonly ?Closure $exceptionLogger = null,
    ) {
        $this->config = is_string($accessTokenOrConfig)
            ? new MaxApiConfig($accessTokenOrConfig)
            : $accessTokenOrConfig;

        $this->httpClient = $this->config->getMaxHttpClient()
            ?? new MaxHttpClient($this->config, null, $this->exceptionLogger);
    }

    /**
     * Добавляет участников в групповой чат или канал.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала с правом `add_remove_members`.
     *
     * @param int $chatId ID группового чата или канала.
     * @param UserIdsList|RawModel|non-empty-array<int> $userIds Список ID пользователей для добавления
     *     в групповой чат или канал.
     *     При использованнии массива, смотрите ограничения в {@see UserIdsList}.
     * @return ModifyMembersResult Результат запроса на изменение списка участников чата.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members
     */
    public function addMembers(int $chatId, UserIdsList|RawModel|array $userIds): ModifyMembersResult
    {
        if (is_array($userIds)) {
            $userIds = new UserIdsList($userIds);
        }

        $data = $this->httpClient->post("/chats/$chatId/members", $userIds->jsonSerialize());

        return ModifyMembersResult::newFromData($data);
    }

    /**
     * Отправляет ответ на Callback.
     *
     * Этот метод используется для отправки ответа после того, как пользователь нажал на кнопку.
     * Ответом может быть обновленное сообщение и/или одноразовое уведомление для пользователя.
     *
     * @param non-empty-string $callbackId Идентификатор кнопки, на которую нажал пользователь
     *                                     (minLength: 1, pattern: '^[\x21-\x7E]+$').
     *                                     Бот получает идентификатор как часть `Update` с типом `message_callback`.
     *                                     Пример получения идентификатора: `$update->getCallback()->getCallbackId()`.
     * @param CallbackAnswer|RawModel|NewMessageBody $answer Ответ на callback: обновленное сообщение и/или уведомление.
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function answerOnCallback(string $callbackId, NewMessageBody|RawModel|CallbackAnswer $answer): void
    {
        self::validateString('callbackId', $callbackId, minLength: 1, pattern: '/^[\x21-\x7E]+$/');

        if ($answer instanceof NewMessageBody) {
            $answer = new CallbackAnswer($answer);
        }

        $data = $this->httpClient->post('/answers', $answer->jsonSerialize(), ['callback_id' => $callbackId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отменяет права администратора в групповом чате или канале.
     *
     * Лишает пользователя или бота прав администратора в групповом чате или канале.
     * При этом из чата и канала они не исключаются.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала с правом `add_admins`.
     *
     * @param int $chatId ID группового чата или канала.
     * @param int $userId Идентификатор пользователя или бота, которого надо лишить прав администратора.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-
     */
    public function deleteAdmin(int $chatId, int $userId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/admins/$userId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет групповой чат.
     *
     * Удаляет групповой чат для всех участников.
     *
     * @param int $chatId ID чата.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-
     * @deprecated
     */
    public function deleteChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет сообщение в чате.
     *
     * Удаляет сообщение в диалоге или чате, если бот имеет разрешение на удаление сообщений.
     *
     * @param non-empty-string $messageId ID удаляемого сообщения (minLength: 1, pattern: '^mid\.[\x21-\x7E]+$').
     * @link https://dev.max.ru/docs-api/methods/DELETE/messages
     */
    public function deleteMessage(string $messageId): void
    {
        self::validateString('messageId', $messageId, minLength: 1, pattern: '/^mid\.[\x21-\x7E]+$/');

        $data = $this->httpClient->delete('/messages', ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Редактирует информацию о групповом чате или канале.
     *
     * Редактирует информацию о групповом чате или канале, включая название, иконку и закреплённое сообщение.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала.
     *
     * @param int $chatId ID чата.
     * @param ChatPatch|RawModel $chatPatch Данные для редактирования чата.
     * @return Chat Обновлённый объект чата.
     * @link https://dev.max.ru/docs-api/methods/PATCH/chats/-chatId-
     */
    public function editChat(int $chatId, ChatPatch|RawModel $chatPatch): Chat
    {
        $data = $this->httpClient->patch("/chats/$chatId", $chatPatch->jsonSerialize());

        return Chat::newFromData($data);
    }

    /**
     * Редактирует сообщение в чате.
     *
     * Метод позволяет редактировать сообщения, отправленные ботом.
     *
     * Ограничения при редактировании сообщений:
     * - В диалогах с ботом:
     *   - сообщения с кнопками `inline_keyboard` редактируются независимо от срока давности.
     *   - остальные сообщения редактируются, если они отправлены менее 7 суток назад.
     * - В групповых чатах и каналах любые сообщения редактируются независимо от срока давности
     *
     * @param non-empty-string $messageId ID редактируемого сообщения (minLength: 1, pattern: '^mid\.[\x21-\x7E]+$').
     * @param NewMessageBody|RawModel|non-empty-string $message Тело нового сообщения.
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function editMessage(string $messageId, NewMessageBody|RawModel|string $message): void
    {
        self::validateString('messageId', $messageId, minLength: 1, pattern: '/^mid\.[\x21-\x7E]+$/');

        if (is_string($message)) {
            $message = new NewMessageBody($message);
        }

        $data = $this->httpClient->put('/messages', $message->jsonSerialize(), ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Редактирует информацию о боте.
     *
     * Редактирует информацию о текущем боте. Позволяет обновить имя, описание, команды и аватар бота.
     *
     * @param BotPatch|RawModel $botPatch Данные для редактирования информации о боте.
     * @return BotInfo Изменённая информация о боте.
     */
    public function editMyInfo(BotPatch|RawModel $botPatch): BotInfo
    {
        $data = $this->httpClient->patch('/me', $botPatch->jsonSerialize());

        return BotInfo::newFromData($data);
    }

    /**
     * Получает список администраторов группового чата или канала.
     *
     * Возвращает список всех администраторов группового чата или канала (пользователей и ботов), их данные,
     * а также права на управление каналом или групповым чатом для пользователей-администраторов.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала.
     *
     * @param int $chatId ID группового чата или канала.
     * @return ChatMembersList Список администраторов в групповом чате или канале.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins
     */
    public function getAdmins(int $chatId): ChatMembersList
    {
        $data = $this->httpClient->get("/chats/$chatId/members/admins");

        return ChatMembersList::newFromData($data);
    }

    /**
     * Получает информацию о групповом чате или канале.
     *
     * Возвращает информацию о групповом чате или канале по его ID.
     *
     * @param int $chatId ID запрашиваемого группового чата или канала.
     * @return Chat Информация о групповом чате или канале.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-
     */
    public function getChat(int $chatId): Chat
    {
        $data = $this->httpClient->get("/chats/$chatId");

        return Chat::newFromData($data);
    }

    /**
     * Получает информацию о канале по его ссылке.
     *
     * Возвращает информацию о канале по его публичной ссылке. Метод доступен только для каналов —
     * получить информацию о чате по публичной ссылке не получится.
     *
     * @param non-empty-string $chatLink Публичная ссылка на канал (minLength: 1, pattern: '@?[a-zA-Z]+[\w-]*').
     * @return Chat Информация о канале.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatLink-
     */
    public function getChatByLink(string $chatLink): Chat
    {
        self::validateString('chatLink', $chatLink, minLength: 1, pattern: '/@?[a-zA-Z]+[\w-]*/');

        $data = $this->httpClient->get("/chats/$chatLink");

        return Chat::newFromData($data);
    }

    /**
     * Получает список всех групповых чатов и каналов.
     *
     * Возвращает список групповых чатов и каналов, в которые добавлен бот,
     * информацию о каждом чате и маркер для перехода к следующей странице списка.
     *
     * @param positive-int $count Количество запрашиваемых чатов (minimum: 1, maximum: 100).
     * @param int|null $marker Указатель на следующую страницу данных. Для первой страницы передайте `null`.
     *
     * @return ChatList В ответе с пагинацией возвращаются чаты.
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     * @deprecated Начиная с июня 2026 этот метод больше не поддерживается, и API не предоставляет готовой
     *     возможности для получения списка групповых чатов и каналов, в которые добавлен бот.
     */
    public function getChats(int $count = 50, ?int $marker = null): ChatList
    {
        self::validateInt('count', $count, 1, 100);

        $params = ['count' => $count];
        if ($marker !== null) {
            $params['marker'] = $marker;
        }

        $data = $this->httpClient->get('/chats', $params);

        return ChatList::newFromData($data);
    }

    /**
     * HTTP-клиент для прямых запросов к Max Messenger API.
     *
     * Для использования необходимо знать формат запросов и ответов API.
     */
    public function getHttpClient(): MaxHttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Получает участников группового чата или канала.
     *
     * Возвращает список участников группового чата или канала и их данные, а также права на управление
     * каналом или групповым чатом для пользователей-администраторов.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала.
     *
     * @param int $chatId ID группового чата или канала.
     * @param int[]|null $userIds Список ID пользователей, чьё членство нужно получить (minItems: 1, uniqueItems: true).
     *                            Когда этот аргумент передан, аргументы `count` и `marker` игнорируются.
     * @param int|null $marker Указатель на следующую страницу данных.
     * @param int<1, 100> $count Максимальное количество участников в ответе (minimum: 1, maximum: 100).
     * @return ChatMembersList Возвращает список участников и указатель на следующую страницу данных.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function getMembers(
        int $chatId,
        ?array $userIds = null,
        ?int $marker = null,
        int $count = 20,
    ): ChatMembersList {
        $params = [];
        if ($userIds !== null) {
            $userIds = array_unique(array_filter($userIds));

            self::validateArray('userIds', $userIds, minItems: 1);

            $params['user_ids'] = implode(',', $userIds);
        } else {
            self::validateInt('count', $count, minimum: 1, maximum: 100);

            if ($marker !== null) {
                $params['marker'] = $marker;
            }
            $params['count'] = $count;
        }

        $data = $this->httpClient->get("/chats/$chatId/members", $params);

        return ChatMembersList::newFromData($data);
    }

    /**
     * Получает информацию о членстве бота в групповом чате или канале.
     *
     * Возвращает информацию о членстве бота в групповом чате или канале, общую информацию о нём,
     * а также список доступных прав доступа.
     *
     * @param int $chatId ID группового чата или канала.
     * @return ChatMember Информация о членстве бота.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/me
     */
    public function getMembership(int $chatId): ChatMember
    {
        $data = $this->httpClient->get("/chats/$chatId/members/me");

        return ChatMember::newFromData($data);
    }

    /**
     * Получает сообщение по ID.
     *
     * Возвращает сообщение по его ID.
     *
     * @param non-empty-string $messageId ID сообщения (`mid`), чтобы получить одно сообщение в чате
     *                                    (pattern: '^mid\.[\x21-\x7E]+$').
     * @return Message Возвращает одно сообщение.
     * @link https://dev.max.ru/docs-api/methods/GET/messages/-messageId-
     */
    public function getMessageById(string $messageId): Message
    {
        self::validateString('messageId', $messageId, minLength: 1, pattern: '/^mid\.[\x21-\x7E]+$/');

        $data = $this->httpClient->get("/messages/$messageId");

        return Message::newFromData($data);
    }

    /**
     * Получает сообщения.
     *
     * Возвращает массив сообщений из чата или указанного списка сообщений.
     * Для выполнения запроса нужно указать один из параметров — `chat_id` или `message_ids`:
     *
     * - `chat_id` — ID чата для получения сообщений из указанного чата. Сообщения возвращаются в обратном порядке:
     *   последние сообщения будут первыми в массиве
     * - `message_ids` — Список ID сообщений (`mid`). Можно указать один идентификатор или несколько
     *
     * @param array<non-empty-string>|null $messageIds Список ID сообщений, которые нужно получить (uniqueItems: true).
     *                                                 Обязательный параметр, если не указан `chatId`.
     * @param int|null $chatId ID чата, чтобы получить сообщения из определённого чата.
     *                         Обязательный параметр, если не указан `messageIds`.
     * @param int|null $from Время, до которого будут запрошены все сообщения с начала чата (в формате Unix timestamp).
     * @param int|null $to Время, начиная с которого будут запрошены все сообщения до конца чата
     *     (в формате Unix timestamp).
     * @param int<1, 100> $count Максимальное количество сообщений в ответе (minimum: 1, maximum: 100).
     * @return MessageList Возвращает список сообщений.
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function getMessages(
        ?array $messageIds = null,
        ?int $chatId = null,
        ?int $from = null,
        ?int $to = null,
        int $count = 50,
    ): MessageList {
        if ($messageIds !== null) {
            self::validateArray('messageIds', $messageIds, minItems: 1);

            $params = ['message_ids' => implode(',', array_unique($messageIds))];
        } else {
            self::validateNotNull('chatId', $chatId);

            $params = ['chat_id' => $chatId];
            if ($from !== null) {
                $params['from'] = $from;
            }
            if ($to !== null) {
                $params['to'] = $to;
            }
            $params['count'] = $count;

            if ($from !== null && $to !== null) {
                self::validateMustBeLess('to', 'from', $to < $from);
            }
        }

        $data = $this->httpClient->get('/messages', $params);

        return MessageList::newFromData($data);
    }

    /**
     * Получение сообщений по ID.
     *
     * Метод возвращает информацию о запрошенных сообщениях. Можно указать один идентификатор или несколько.
     *
     * @param array<non-empty-string> $messageIds Список ID сообщений, которые нужно получить.
     * @return MessageList Возвращает список сообщений.
     */
    public function getMessagesById(array $messageIds): MessageList
    {
        return $this->getMessages($messageIds);
    }

    /**
     * Получение сообщений из чата.
     *
     * Метод возвращает массив сообщений из указанного чата.
     * Сообщения возвращаются в обратном порядке: последние сообщения будут первыми в массиве.
     *
     * @param int $chatId ID чата, чтобы получить сообщения из определённого чата.
     * @param int|null $from Время, до которого будут запрошены все сообщения с начала чата (в формате Unix timestamp).
     * @param int|null $to Время, начиная с которого будут запрошены все сообщения до конца чата
     *     (в формате Unix timestamp).
     * @param int<1, 100> $count Максимальное количество сообщений в ответе (minimum: 1, maximum: 100).
     * @return MessageList Возвращает список сообщений.
     */
    public function getMessagesFromChat(
        int $chatId,
        ?int $from = null,
        ?int $to = null,
        int $count = 50,
    ): MessageList {
        return $this->getMessages(null, $chatId, $from, $to, $count);
    }

    /**
     * Получает информацию о боте.
     *
     * Возвращает информацию о боте, от имени которого выполняется запрос.
     *
     * @return BotInfo Информация о боте.
     * @link https://dev.max.ru/docs-api/methods/GET/me
     */
    public function getMyInfo(): BotInfo
    {
        $data = $this->httpClient->get('/me');

        return BotInfo::newFromData($data);
    }

    /**
     * Получает закреплённое сообщение в групповом чате или канале.
     *
     * Возвращает закреплённое сообщение в групповом чате или канале.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала.
     *
     * @param int $chatId ID чата.
     * @return GetPinnedMessageResult Закреплённое сообщение.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/pin
     */
    public function getPinnedMessage(int $chatId): GetPinnedMessageResult
    {
        $data = $this->httpClient->get("/chats/$chatId/pin");

        return GetPinnedMessageResult::newFromData($data);
    }

    /**
     * Получает Webhook подписки.
     *
     * Возвращает список всех Webhook подписок данного бота.
     *
     * @return GetSubscriptionsResult Список подписок.
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function getSubscriptions(): GetSubscriptionsResult
    {
        $data = $this->httpClient->get('/subscriptions');

        return GetSubscriptionsResult::newFromData($data);
    }

    /**
     * Получает новые события.
     *
     * Выполняет долгий запрос (Long Polling). Каждое событие имеет свой номер последовательности.
     * Свойство `marker` в ответе указывает на следующее ожидаемое событие.
     *
     * Если параметр `marker` **не передан**, бот получит все ранее не полученные событие.
     *
     * > Этот метод можно использовать для получения событий бота при разработке и тестировании,
     * > если ваш бот не подписан на доставку событий через Webhook.
     * > Для production-окружения рекомендуем использовать доставку событий через Webhook.
     *
     * @param int<1, 1000> $limit Максимальное количество событий для получения (minimum: 1, maximum: 1000).
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса (minimum: 0, maximum: 90).
     * @param int|null $marker Маркер для получения событий с конкретной позиции.
     *                         Для получения всех ранее непрочитанных событий, передайте `null`.
     * @param array<UpdateType|string>|null $types Список типов событий, которые вы хотите получить (uniqueItems: true).
     * @return UpdateList Список событий.
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function getUpdates(
        int $limit = 100,
        int $timeout = 60,
        ?int $marker = null,
        ?array $types = null,
    ): UpdateList {
        $params = [
            'limit'   => $limit,
            'timeout' => $timeout,
        ];

        if ($marker !== null) {
            $params['marker'] = $marker;
        }

        if ($types !== null) {
            $params['types'] = implode(',', array_unique($this->convertEnumsToStrings($types)));
        }

        $data = $this->httpClient->get('/updates', $params, ($timeout + 10) * 1000);

        return UpdateList::newFromData($data);
    }

    /**
     * Получает URL для загрузки файлов.
     *
     * Возвращает URL для последующей загрузки файла.
     *
     * @param UploadType $type Тип загружаемого файла.
     * @return UploadEndpoint Возвращает URL для загрузки вложения.
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function getUploadUrl(UploadType $type): UploadEndpoint
    {
        $data = $this->httpClient->post('/uploads', null, ['type' => $type->value]);

        return UploadEndpoint::newFromData($data);
    }

    /**
     * Получает информацию о видео.
     *
     * Возвращает подробную информацию о прикреплённом видео (URL-адреса воспроизведения и дополнительные метаданные).
     *
     * @param non-empty-string $videoToken Токен видео-вложения (minLength: 1, pattern: '^vid\.[\x21-\x7E]+$').
     * @return VideoAttachmentDetails Подробная информация о видео.
     * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-
     */
    public function getVideoAttachmentDetails(string $videoToken): VideoAttachmentDetails
    {
        self::validateString('videoToken', $videoToken, minLength: 1, pattern: '/^vid\.[\x21-\x7E]+$/');

        $data = $this->httpClient->get("/videos/$videoToken");

        return VideoAttachmentDetails::newFromData($data);
    }

    /**
     * Удаляет бота из группового чата или канала.
     *
     * Удаляет бота из участников группового чата или канала.
     *
     * @param int $chatId ID группового чата или канала.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/me
     */
    public function leaveChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/me");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Закрепляет сообщение в групповом чате или канале.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала.
     *
     * @param int $chatId ID группового чата или канала, где нужно закрепить сообщение.
     * @param PinMessageBody|RawModel|non-empty-string $pinMessage Сообщение для закрепления.
     * @link https://dev.max.ru/docs-api/methods/PUT/chats/-chatId-/pin
     */
    public function pinMessage(int $chatId, PinMessageBody|RawModel|string $pinMessage): void
    {
        if (is_string($pinMessage)) {
            $pinMessage = new PinMessageBody($pinMessage);
        }

        $data = $this->httpClient->put("/chats/$chatId/pin", $pinMessage->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Назначает администраторов группового чата или канала.
     *
     * Выдаёт пользователям и ботам, которые являются участниками чата или подписчиками канала,
     * права администратора. Максимум 50 администраторов в чате.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала с правом `add_admins`.
     *
     * Права, которые можно назначить, зависят от того, где (канал или чат) и кому (пользователь или бот)
     * выданы. Если вы хотите изменить назначенные права, вызовите повторно текущий метод с обновлённым
     * списком прав. Полный список доступных прав администратора и условия их назначения описаны
     * в классе `ChatAdminPermission`.
     *
     * @param int $chatId ID группового чата или канала.
     * @param ChatAdminsList|RawModel|ChatAdmin[] $admins Список администраторов.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/admins
     */
    public function postAdmins(int $chatId, ChatAdminsList|RawModel|array $admins): void
    {
        if (is_array($admins)) {
            $admins = new ChatAdminsList($admins);
        }

        $data = $this->httpClient->post("/chats/$chatId/members/admins", $admins->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет участника из группового чата или канала.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала с правом `add_remove_members`.
     *
     * @param int $chatId ID группового чата или канала.
     * @param int $userId ID пользователя, которого нужно удалить из группового чата или канала.
     * @param bool $block Если передать `true`, пользователь будет заблокирован в чате.
     *                    Применяется только для чатов с публичной или приватной ссылкой.
     *                    Игнорируется в остальных случаях.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members
     */
    public function removeMember(int $chatId, int $userId, bool $block = false): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members", [
            'user_id' => $userId,
            'block'   => $block ? 'true' : 'false',
        ]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отправляет действие бота в групповой чат.
     *
     * Отправляет в групповой чат такие действия бота, как например: «набор текста» или «отправка фото».
     *
     * @param int $chatId ID чата.
     * @param ActionRequestBody|RawModel|SenderAction $action Действие бота.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/actions
     */
    public function sendAction(int $chatId, ActionRequestBody|RawModel|SenderAction $action): void
    {
        if ($action instanceof SenderAction) {
            $action = new ActionRequestBody($action);
        }

        $data = $this->httpClient->post("/chats/$chatId/actions", $action->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отправляет сообщение в чат.
     *
     * Возвращает созданное сообщение.
     *
     * @param int|null $userId Если вы хотите отправить сообщение пользователю, укажите его ID.
     * @param int|null $chatId Если сообщение отправляется в чат, укажите его ID.
     * @param NewMessageBody|RawModel|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function sendMessage(
        ?int $userId,
        ?int $chatId,
        NewMessageBody|RawModel|string $message,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        $params = [];
        if ($userId !== null && $chatId !== null) {
            throw new RequiredArgumentsException('At least one argument (userId, chatId) must be non-null.');
        }
        if ($chatId !== null) {
            $params['chat_id'] = $chatId;
        } else {
            self::validateNotNull('user_id', $userId);

            $params['user_id'] = $userId;
        }
        if ($disableLinkPreview) {
            $params['disable_link_preview'] = 'true';
        }

        if (is_string($message)) {
            $message = new NewMessageBody($message);
        }

        $retryAttempts = $this->retryAttempts ?? $this->config->getRetryAttempts();
        do {
            try {
                $data = $this->httpClient->post('/messages', $message->jsonSerialize(), $params);

                return SendMessageResult::newFromData($data);
            } catch (BadRequestException $e) {
                if (!$retryAttempts || !$e->isAttachmentNotReady()) {
                    throw $e;
                }
                if ($this->exceptionLogger !== null) {
                    ($this->exceptionLogger)(__METHOD__, $e);
                }
                usleep(array_shift($retryAttempts) * 1000);
            }
        } while (true);
    }

    /**
     * Отправить сообщение в чат.
     *
     * Отправляет сообщение в чат. В результате метода возвращается идентификатор нового сообщения.
     *
     * @param int $chatId ID чата.
     * @param NewMessageBody|RawModel|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendMessageToChat(
        int $chatId,
        NewMessageBody|RawModel|string $message,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->sendMessage(null, $chatId, $message, $disableLinkPreview);
    }

    /**
     * Отправить сообщение пользователю.
     *
     * Отправляет сообщение в диалог. В результате метода возвращается идентификатор нового сообщения.
     *
     * @param int $userId ID пользователя.
     * @param NewMessageBody|RawModel|non-empty-string $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendMessageToUser(
        int $userId,
        NewMessageBody|RawModel|string $message,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->sendMessage($userId, null, $message, $disableLinkPreview);
    }

    /**
     * Подписывает на доставку событий бота через Webhook.
     *
     * Настраивает доставку событий бота через Webhook — основной механизм получения событий в продуктовых интеграциях.
     * При активной подписке Long Polling не работает.
     *
     * После вызова этого метода, события отправляются на указанный Webhook-endpoint
     * в виде HTTPS POST-запросов с объектом {@see Update}.
     *
     * > Для повышения безопасности с 25 мая прекращена поддержка получения событий по HTTP,
     * > а также самоподписанных сертификатов.
     *
     * > Webhook-endpoint должен возвращать **HTTP 200** в течение 30 секунд.
     * > Любой другой код ответа или превышение тайм-аута — ошибка доставки.
     *
     * @param SubscriptionRequestBody|RawModel $subscription Параметры подписки.
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public function subscribe(SubscriptionRequestBody|RawModel $subscription): void
    {
        $data = $this->httpClient->post('/subscriptions', $subscription->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Открепляет сообщение в групповом чате или канале.
     *
     * Бот, чей токен `access_token` используется для авторизации, должен быть администратором этого
     * чата или канала.
     *
     * @param int $chatId ID группового чата или канала, в котором нужно открепить сообщение или пост.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/pin
     */
    public function unpinMessage(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/pin");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отписывает от доставки событий бота через Webhook.
     *
     * Отписывает бота от получения новых событий через Webhook.
     *
     * После вызова этого метода бот перестаёт получать новые события через Webhook и становится доступна
     * доставка уведомлений через Long Polling (метод с длительным опросом).
     *
     * @param non-empty-string $url URL, который нужно удалить из подписок на WebHook.
     * @link https://dev.max.ru/docs-api/methods/DELETE/subscriptions
     */
    public function unsubscribe(string $url): void
    {
        $data = $this->httpClient->delete('/subscriptions', ['url' => $url]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Проверяет соответствие хеша и значения vcfInfo.
     *
     * Позволяет проверить, что пользователь поделился номером телефона, привязанным к его аккаунту в МАКС.
     *
     * Если проверка прошла успешно, Вы можете получить номер телефона следующим способом:
     * ```
     * $phones = $payload->getPhones();
     * ```
     *
     * @see MessageCreatedEvent::isSelfContact()
     */
    public function validateContactAttachmentHash(ContactAttachmentPayload $payload): bool
    {
        $hash = $payload->getHash();
        $vcfInfo = $payload->getVcfInfo();
        $accessToken = $this->config->getAccessToken()?->getValue();

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (!$hash || !$vcfInfo || !$accessToken) {
            return false;
        }

        $validHash = hash_hmac('sha256', $vcfInfo, $accessToken);

        return hash_equals($hash, $validHash);
    }

    private function checkSimpleQueryResult(array $data): void
    {
        $result = SimpleQueryResult::newFromData($data);

        if (!$result->isSuccess()) {
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            throw new SimpleQueryError($result->getMessage() ?: 'The server did not return the message.');
        }
    }

    /**
     * @param array<BackedEnum|string|int> $arr
     * @return array<string|int>
     */
    private function convertEnumsToStrings(array $arr): array
    {
        foreach ($arr as &$item) {
            if ($item instanceof BackedEnum) {
                $item = $item->value;
            }
        }

        /** @psalm-var array<string|int> */
        return $arr;
    }
}
