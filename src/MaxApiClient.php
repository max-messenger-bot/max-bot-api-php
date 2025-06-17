<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use BackedEnum;
use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Exceptions\RequiredArgumentsException;
use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\HttpClient\Exceptions\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use MaxMessenger\Bot\Models\Enums\SenderAction;
use MaxMessenger\Bot\Models\Enums\UpdateType;
use MaxMessenger\Bot\Models\Enums\UploadType;
use MaxMessenger\Bot\Models\Requests\ActionRequestBody;
use MaxMessenger\Bot\Models\Requests\BotPatch;
use MaxMessenger\Bot\Models\Requests\CallbackAnswer;
use MaxMessenger\Bot\Models\Requests\ChatAdmin;
use MaxMessenger\Bot\Models\Requests\ChatAdminsList;
use MaxMessenger\Bot\Models\Requests\ChatPatch;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;
use MaxMessenger\Bot\Models\Requests\PinMessageBody;
use MaxMessenger\Bot\Models\Requests\RawModel;
use MaxMessenger\Bot\Models\Requests\SubscriptionRequestBody;
use MaxMessenger\Bot\Models\Requests\UserIdsList;
use MaxMessenger\Bot\Models\Requests\ValidateTrait;
use MaxMessenger\Bot\Models\Responses\BotInfo;
use MaxMessenger\Bot\Models\Responses\Chat;
use MaxMessenger\Bot\Models\Responses\ChatList;
use MaxMessenger\Bot\Models\Responses\ChatMember;
use MaxMessenger\Bot\Models\Responses\ChatMembersList;
use MaxMessenger\Bot\Models\Responses\GetPinnedMessageResult;
use MaxMessenger\Bot\Models\Responses\GetSubscriptionsResult;
use MaxMessenger\Bot\Models\Responses\Message;
use MaxMessenger\Bot\Models\Responses\MessageList;
use MaxMessenger\Bot\Models\Responses\ModifyMembersResult;
use MaxMessenger\Bot\Models\Responses\SendMessageResult;
use MaxMessenger\Bot\Models\Responses\SimpleQueryResult;
use MaxMessenger\Bot\Models\Responses\UpdateList;
use MaxMessenger\Bot\Models\Responses\UploadEndpoint;
use MaxMessenger\Bot\Models\Responses\VideoAttachmentDetails;
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
     * @var list<positive-int>
     */
    public array $retryAttempts = [1, 2, 4, 8, 15];
    private readonly MaxHttpClient $httpClient;

    public function __construct(
        #[SensitiveParameter]
        string|MaxApiConfigInterface $accessTokenOrConfig
    ) {
        $config = is_string($accessTokenOrConfig)
            ? new MaxApiConfig(accessToken: $accessTokenOrConfig)
            : $accessTokenOrConfig;

        $this->httpClient = new MaxHttpClient($config);
    }

    /**
     * Добавляет участников в групповой чат.
     *
     * Для этого могут потребоваться дополнительные права.
     *
     * @param int $chatId ID чата.
     * @param UserIdsList|RawModel|int[] $userIds Список ID пользователей для добавления в чат.
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
     * @param non-empty-string $callbackId Идентификатор кнопки, по которой пользователь кликнул
     *                                     (minLength: 1, pattern: '^\s+$').
     *                                     Бот получает идентификатор как часть `Update` с типом `message_callback`.
     *                                     Можно получить из `$update->getCallback()->getCallbackId()`.
     * @param CallbackAnswer|RawModel|NewMessageBody $answer Ответ на callback: обновленное сообщение и/или уведомление.
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function answerOnCallback(string $callbackId, NewMessageBody|RawModel|CallbackAnswer $answer): void
    {
        self::validateString('callbackId', $callbackId, minLength: 1, pattern: '/^\s+$/');

        if ($answer instanceof NewMessageBody) {
            $answer = new CallbackAnswer($answer);
        }

        $data = $this->httpClient->post('/answers', $answer->jsonSerialize(), ['callback_id' => $callbackId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отменяет права администратора в групповом чате.
     *
     * Отменяет права администратора у пользователя в групповом чате, лишая его административных привилегий.
     *
     * @param int $chatId ID чата.
     * @param int $userId Идентификатор пользователя.
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
     */
    public function deleteChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет сообщение в чате.
     *
     * Бот должен иметь разрешение на удаление сообщений.
     *
     * > С помощью метода можно удалять сообщения, которые отправлены менее 24 часов назад.
     *
     * @param non-empty-string $messageId ID удаляемого сообщения (minLength: 1, pattern: '^mid\.[0-9a-f]+$').
     * @link https://dev.max.ru/docs-api/methods/DELETE/messages
     */
    public function deleteMessage(string $messageId): void
    {
        self::validateString('messageId', $messageId, minLength: 1, pattern: '/^mid\.[0-9a-f]+$/');

        $data = $this->httpClient->delete('/messages', ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Редактирует информацию о групповом чате.
     *
     * Редактирует информацию о групповом чате, включая название, иконку и закреплённое сообщение.
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
     * Если поле `attachments` не установлено, вложения текущего сообщения не изменяются.
     * Если в этом поле передан пустой список, все вложения будут удалены
     *
     * > С помощью метода можно отредактировать сообщения, которые отправлены менее 24 часов назад.
     *
     * @param non-empty-string $messageId ID редактируемого сообщения (minLength: 1, pattern: '^mid\.[0-9a-f]+$').
     * @param NewMessageBody|RawModel $message Тело нового сообщения.
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function editMessage(string $messageId, NewMessageBody|RawModel $message): void
    {
        self::validateString('messageId', $messageId, minLength: 1, pattern: '/^mid\.[0-9a-f]+$/');

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
     * Получает список администраторов группового чата.
     *
     * Возвращает список всех администраторов группового чата. Бот должен быть администратором в запрашиваемом чате.
     *
     * @param int $chatId ID чата.
     * @return ChatMembersList Список администраторов.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins
     */
    public function getAdmins(int $chatId): ChatMembersList
    {
        $data = $this->httpClient->get("/chats/$chatId/members/admins");

        return ChatMembersList::newFromData($data);
    }

    /**
     * Получает информацию о групповом чате.
     *
     * Возвращает информацию о групповом чате по его ID.
     *
     * @param int $chatId ID запрашиваемого чата.
     * @return Chat Информация о чате.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-
     */
    public function getChat(int $chatId): Chat
    {
        $data = $this->httpClient->get("/chats/$chatId");

        return Chat::newFromData($data);
    }

    /**
     * Получает список всех групповых чатов.
     *
     * Возвращает список групповых чатов, в которых участвовал бот,
     * информацию о каждом чате и маркер для перехода к следующей странице списка.
     *
     * @param positive-int $count Количество запрашиваемых чатов (minimum: 1, maximum: 100).
     * @param int|null $marker Указатель на следующую страницу данных. Для первой страницы передайте `null`.
     * @return ChatList В ответе с пагинацией возвращаются чаты.
     * @link https://dev.max.ru/docs-api/methods/GET/chats
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
    public function getHttpClient(): MaxHttpClient
    {
        return $this->httpClient;
    }

    /**
     * Получает участников группового чата.
     *
     * Возвращает список участников группового чата.
     *
     * @param int $chatId ID чата.
     * @param int[]|null $userIds Список ID пользователей для получения их членства (minItems: 1, uniqueItems: true).
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
        int $count = 20
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
     * Получает информацию о членстве бота в групповом чате.
     *
     * Возвращает информацию о членстве текущего бота в групповом чате. Бот идентифицируется с помощью токена доступа.
     *
     * @param int $chatId ID чата.
     * @return ChatMember Текущая информация о членстве бота.
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
     *                                    (pattern: '^mid\.[0-9a-f]+$').
     * @return Message Возвращает одно сообщение.
     * @link https://dev.max.ru/docs-api/methods/GET/messages/-messageId-
     */
    public function getMessageById(string $messageId): Message
    {
        self::validateString('messageId', $messageId, minLength: 1, pattern: '/^mid\.[0-9a-f]+$/');

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
     * @param int|null $from Время начала для запрашиваемых сообщений (в формате Unix timestamp).
     * @param int|null $to Время окончания для запрашиваемых сообщений (в формате Unix timestamp).
     * @param int<1, 100> $count Максимальное количество сообщений в ответе (minimum: 1, maximum: 100).
     * @return MessageList Возвращает список сообщений.
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function getMessages(
        ?array $messageIds = null,
        ?int $chatId = null,
        ?int $from = null,
        ?int $to = null,
        int $count = 50
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
     * @param int|null $from Время начала для запрашиваемых сообщений (в формате Unix timestamp).
     * @param int|null $to Время окончания для запрашиваемых сообщений (в формате Unix timestamp).
     * @param int<1, 100> $count Максимальное количество сообщений в ответе (minimum: 1, maximum: 100).
     * @return MessageList Возвращает список сообщений.
     */
    public function getMessagesFromChat(
        int $chatId,
        ?int $from = null,
        ?int $to = null,
        int $count = 50
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
     * Получает закреплённое сообщение в групповом чате.
     *
     * Возвращает закреплённое сообщение в групповом чате.
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
     * Получает подписки.
     *
     * Возвращает список всех подписок данного бота.
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
     * Получает обновления.
     *
     * Выполняет долгий запрос (long polling). Каждое обновление имеет свой номер последовательности.
     * Свойство `marker` в ответе указывает на следующее ожидаемое обновление.
     *
     * Если параметр `marker` **не передан**, бот получит все ранее не полученные обновления.
     *
     * > Этот метод можно использовать для получения обновлений при разработке и тестировании,
     * если ваш бот не подписан на Webhook. Для production-окружения рекомендуем использовать Webhook.
     *
     * @param int<1, 1000> $limit Максимальное количество обновлений для получения (minimum: 1, maximum: 1000).
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса (minimum: 0, maximum: 90).
     * @param int|null $marker Маркер для получения обновлений с конкретной позиции.
     *                         Для получения всех ранее непрочитанных обновлений, передайте `null`.
     * @param array<UpdateType|string>|null $types Список типов обновлений, которые бот хочет получить
     *                                             (uniqueItems: true).
     * @return UpdateList Список обновлений.
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function getUpdates(
        int $limit = 100,
        int $timeout = 60,
        ?int $marker = null,
        ?array $types = null
    ): UpdateList {
        $params = [
            'limit' => $limit,
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
     * @param non-empty-string $videoToken Токен видео-вложения (minLength: 1, pattern: '^[a-zA-Z0-9\+/_-]+={0-2}$').
     * @return VideoAttachmentDetails Подробная информация о видео.
     * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-
     */
    public function getVideoAttachmentDetails(string $videoToken): VideoAttachmentDetails
    {
        self::validateString('videoToken', $videoToken, minLength: 1, pattern: '/^vid\.[0-9a-f]+$/');

        $data = $this->httpClient->get("/videos/$videoToken");

        return VideoAttachmentDetails::newFromData($data);
    }

    /**
     * Удаляет бота из группового чата.
     *
     * Удаляет бота из участников группового чата.
     *
     * @param int $chatId ID чата.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/me
     */
    public function leaveChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/me");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Закрепляет сообщение в групповом чате.
     *
     * Требуются права на закрепление сообщений в чате.
     *
     * @param int $chatId ID чата, где должно быть закреплено сообщение.
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
     * Удаляет участника из группового чата.
     *
     * Для этого требуются права на удаление участника из группового чата.
     *
     * @param int $chatId ID чата.
     * @param int $userId Идентификатор пользователя для удаления из чата.
     * @param bool $block Если установлено в `true`, пользователь будет заблокирован в чате.
     *                    Применяется только для чатов с публичной или приватной ссылкой.
     *                    Игнорируется в остальных случаях.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members
     */
    public function removeMember(int $chatId, int $userId, bool $block = false): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members", [
            'user_id' => $userId,
            'block' => $block ? 'true' : 'false',
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
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function sendMessage(
        ?int $userId,
        ?int $chatId,
        NewMessageBody|RawModel|string $message,
        bool $disableLinkPreview = false
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

        $retryAttempts = $this->retryAttempts;
        do {
            try {
                $data = $this->httpClient->post('/messages', $message->jsonSerialize(), $params);

                return SendMessageResult::newFromData($data);
            } catch (BadRequestException $e) {
                if (!$retryAttempts || !$e->isAttachmentNotReady()) {
                    throw $e;
                }
                sleep(array_shift($retryAttempts));
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
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendMessageToChat(
        int $chatId,
        NewMessageBody|RawModel|string $message,
        bool $disableLinkPreview = false
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
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendMessageToUser(
        int $userId,
        NewMessageBody|RawModel|string $message,
        bool $disableLinkPreview = false
    ): SendMessageResult {
        return $this->sendMessage($userId, null, $message, $disableLinkPreview);
    }

    /**
     * Назначает администраторов группового чата.
     *
     * Возвращает значение `true`, если в групповой чат добавлены все администраторы.
     *
     * @param int $chatId ID чата.
     * @param ChatAdminsList|RawModel|ChatAdmin[] $admins Список администраторов.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/admins
     */
    public function setAdmins(int $chatId, ChatAdminsList|RawModel|array $admins): void
    {
        if (is_array($admins)) {
            $admins = new ChatAdminsList($admins);
        }

        $data = $this->httpClient->post("/chats/$chatId/members/admins", $admins->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Подписывает на обновления.
     *
     * Настраивает доставку событий бота через Webhook — основной механизм получения событий в продуктовых интеграциях.
     * При активной подписке Long Polling не работает.
     *
     * После вызова метода события отправляются на указанный Webhook-endpoint
     * в виде HTTPS POST-запросов с объектом `Update`.
     *
     * > Webhook-endpoint должен возвращать **HTTP 200** в течение 30 секунд.\
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
     * Удаляет закреплённое сообщение в групповом чате.
     *
     * @param int $chatId ID чата, из которого нужно удалить закреплённое сообщение.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/pin
     */
    public function unpinMessage(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/pin");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отписывает от обновлений.
     *
     * Отписывает бота от получения обновлений через Webhook. После вызова этого метода бот перестаёт получать
     * уведомления о новых событиях, и становится доступна доставка уведомлений через API с длительным опросом.
     *
     * @param non-empty-string $url URL, который нужно удалить из подписок на WebHook.
     * @link https://dev.max.ru/docs-api/methods/DELETE/subscriptions
     */
    public function unsubscribe(string $url): void
    {
        $data = $this->httpClient->delete('/subscriptions', ['url' => $url]);

        $this->checkSimpleQueryResult($data);
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
