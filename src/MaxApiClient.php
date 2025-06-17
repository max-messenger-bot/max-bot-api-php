<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use BackedEnum;
use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Exceptions\ActionProhibited;
use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\Exceptions\Validation\MatchException;
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
use MaxMessenger\Bot\Models\Responses\SendMessageResult;
use MaxMessenger\Bot\Models\Responses\SimpleQueryResult;
use MaxMessenger\Bot\Models\Responses\UpdateList;
use MaxMessenger\Bot\Models\Responses\UploadEndpoint;
use MaxMessenger\Bot\Models\Responses\VideoAttachmentDetails;
use SensitiveParameter;

use function is_array;
use function is_string;

/**
 * @api
 */
final class MaxApiClient
{
    use ValidateTrait;

    private readonly MaxHttpClient $httpClient;

    /**
     * @api
     */
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
     * Добавление участников в групповой чат.
     *
     * Добавляет участников в групповой чат. Для этого могут потребоваться дополнительные права.
     *
     * @param int $chatId ID чата.
     * @param int[]|UserIdsList|RawModel $userIds Список ID пользователей для добавления в чат.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members
     * @api
     */
    public function addMembers(int $chatId, array|UserIdsList|RawModel $userIds): void
    {
        if (is_array($userIds)) {
            $userIds = new UserIdsList($userIds);
        }

        $data = $this->httpClient->post("/chats/$chatId/members", $userIds->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Ответ на callback.
     *
     * Этот метод используется для отправки ответа после того, как пользователь нажал на кнопку.
     * Ответом может быть обновленное сообщение и/или одноразовое уведомление для пользователя.
     *
     * @param non-empty-string $callbackId Идентификатор кнопки, по которой пользователь кликнул.
     *     Бот получает идентификатор как часть Update с типом `message_callback`.
     *     Можно получить из GET:/updates через поле `updates[i].callback.callback_id`.
     * @param CallbackAnswer|RawModel $answer Ответ на callback: обновленное сообщение и/или уведомление.
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     * @api
     */
    public function answerOnCallback(string $callbackId, CallbackAnswer|RawModel $answer): void
    {
        static::validateString('callbackId', $callbackId, minLength: 1);

        $data = $this->httpClient->post('/answers', $answer->jsonSerialize(), ['callback_id' => $callbackId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отменить права администратора в групповом чате.
     *
     * Отменяет права администратора у пользователя в групповом чате, лишая его административных привилегий.
     *
     * @param int $chatId ID чата.
     * @param int $userId Идентификатор пользователя.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-
     * @api
     */
    public function deleteAdmin(int $chatId, int $userId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/admins/$userId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаление группового чата.
     *
     * Удаляет групповой чат для всех участников.
     *
     * @param int $chatId ID чата.
     * @lhttps://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-
     * @api
     */
    public function deleteChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удалить сообщение.
     *
     * Удаляет сообщение в диалоге или чате, если бот имеет разрешение на удаление сообщений.
     *
     * С помощью метода можно удалять сообщения, которые отправлены менее 24 часов назад.
     *
     * @param non-empty-string $messageId ID удаляемого сообщения.
     * @link https://dev.max.ru/docs-api/methods/DELETE/messages
     * @api
     */
    public function deleteMessage(string $messageId): void
    {
        $data = $this->httpClient->delete('/messages', ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Изменение информации о групповом чате.
     *
     * Позволяет редактировать информацию о групповом чате, включая название, иконку и закреплённое сообщение.
     *
     * @param int $chatId ID чата.
     * @param ChatPatch|RawModel $chatPatch Данные для редактирования чата.
     * @return Chat Обновлённый объект чата.
     * @link https://dev.max.ru/docs-api/methods/PATCH/chats/-chatId-
     * @api
     */
    public function editChat(int $chatId, ChatPatch|RawModel $chatPatch): Chat
    {
        $data = $this->httpClient->patch("/chats/$chatId", $chatPatch->jsonSerialize());

        return Chat::newFromData($data);
    }

    /**
     * Редактировать сообщение.
     *
     * Редактирует сообщение в чате. Если поле `attachments` равно `null`, вложения текущего сообщения
     * не изменяются. Если в этом поле передан пустой список, все вложения будут удалены.
     *
     * С помощью метода можно отредактировать сообщения, которые отправлены менее 24 часов назад.
     *
     * @param non-empty-string $messageId ID редактируемого сообщения (минимум: 1).
     * @param NewMessageBody|RawModel $message Тело нового сообщения.
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     * @api
     */
    public function editMessage(string $messageId, NewMessageBody|RawModel $message): void
    {
        static::validateString('messageId', $messageId, minLength: 1);

        $data = $this->httpClient->put('/messages', $message->jsonSerialize(), ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Редактирование информации о боте.
     *
     * Редактирует информацию о текущем боте.
     *
     * @param BotPatch|RawModel $botPatch Данные для редактирования информации о боте.
     * @return BotInfo Изменённая информация о боте.
     * @api
     */
    public function editMyInfo(BotPatch|RawModel $botPatch): BotInfo
    {
        $data = $this->httpClient->patch('/me', $botPatch->jsonSerialize());

        return BotInfo::newFromData($data);
    }

    /**
     * Получение списка администраторов группового чата.
     *
     * Возвращает список всех администраторов группового чата. Бот должен быть администратором в запрашиваемом чате.
     *
     * @param int $chatId ID чата.
     * @return ChatMembersList Список администраторов.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins
     * @api
     */
    public function getAdmins(int $chatId): ChatMembersList
    {
        $data = $this->httpClient->get("/chats/$chatId/members/admins");

        return ChatMembersList::newFromData($data);
    }

    /**
     * Получение информации о групповом чате.
     *
     * Возвращает информацию о групповом чате по его ID.
     *
     * @param int $chatId ID запрашиваемого чата.
     * @return Chat Информация о чате.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-
     * @api
     */
    public function getChat(int $chatId): Chat
    {
        $data = $this->httpClient->get("/chats/$chatId");

        return Chat::newFromData($data);
    }

    /**
     * Получение списка всех групповых чатов.
     *
     * Возвращает список групповых чатов, в которых участвовал бот, информацию о каждом чате
     * и маркер для перехода к следующей странице списка.
     *
     * @param positive-int $count Количество запрашиваемых чатов (минимум: 1, максимум: 100).
     * @param int|null $marker Указатель на следующую страницу данных. Для первой страницы передайте `null`.
     * @return ChatList В ответе с пагинацией возвращаются чаты.
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     * @api
     */
    public function getChats(int $count = 50, ?int $marker = null): ChatList
    {
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
     *
     * @api
     */
    public function getHttpClient(): MaxHttpClient
    {
        return $this->httpClient;
    }

    /**
     * Получение участников группового чата.
     *
     * Возвращает список участников группового чата.
     *
     * @param int $chatId ID чата.
     * @param int[]|null $userIds Список ID пользователей для получения их членства.
     *     Когда этот параметр передан, оба `count` и `marker` игнорируются.
     * @param int|null $marker Указатель на следующую страницу данных.
     * @param int<1, 100> $count Максимальное количество участников в ответе (минимум: 1, максимум: 100).
     * @return ChatMembersList Возвращает список участников и указатель на следующую страницу данных.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     * @api
     */
    public function getMembers(
        int $chatId,
        ?array $userIds = null,
        ?int $marker = null,
        int $count = 20
    ): ChatMembersList {
        $params = [];
        if ($userIds !== null) {
            static::validateArray('userIds', $userIds, minItems: 1);

            $params['user_ids'] = implode(',', array_unique($userIds));
        } else {
            if ($marker !== null) {
                $params['marker'] = $marker;
            }
            $params['count'] = $count;
        }

        $data = $this->httpClient->get("/chats/$chatId/members", $params);

        return ChatMembersList::newFromData($data);
    }

    /**
     * Получение информации о членстве бота в групповом чате.
     *
     * Возвращает информацию о членстве текущего бота в групповом чате. Бот идентифицируется с помощью токена доступа.
     *
     * @param int $chatId ID чата.
     * @return ChatMember Текущая информация о членстве бота.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/me
     * @api
     */
    public function getMembership(int $chatId): ChatMember
    {
        $data = $this->httpClient->get("/chats/$chatId/members/me");

        return ChatMember::newFromData($data);
    }

    /**
     * Получить сообщение.
     *
     * Возвращает сообщение по его ID.
     *
     * @param non-empty-string $messageId ID сообщения (`mid`), чтобы получить одно сообщение в чате.
     * @return Message Возвращает одно сообщение.
     * @link https://dev.max.ru/docs-api/methods/GET/messages/-messageId-
     * @api
     */
    public function getMessageById(string $messageId): Message
    {
        $data = $this->httpClient->get("/messages/$messageId");

        return Message::newFromData($data);
    }

    /**
     * Получение сообщений.
     *
     * Метод возвращает информацию о сообщении или массив сообщений из чата.
     * Для выполнения запроса нужно указать один из параметров — `chat_id` или `message_ids`:
     *
     * - `chat_id` — метод возвращает массив сообщений из указанного чата.
     *   Сообщения возвращаются в обратном порядке: последние сообщения будут первыми в массиве.
     * - `message_ids` — метод возвращает информацию о запрошенных сообщениях.
     *   Можно указать один идентификатор или несколько.
     *
     * @param array<non-empty-string>|null $messageIds Список ID сообщений, которые нужно получить (через запятую).
     *     Обязательный параметр, если не указан `chatId`.
     * @param int|null $chatId ID чата, чтобы получить сообщения из определённого чата.
     *     Обязательный параметр, если не указан `messageIds`.
     * @param int|null $from Время начала для запрашиваемых сообщений (в формате Unix timestamp).
     * @param int|null $to Время окончания для запрашиваемых сообщений (в формате Unix timestamp).
     * @param int<1, 100> $count Максимальное количество сообщений в ответе (минимум: 1, максимум: 100).
     * @return MessageList Возвращает список сообщений.
     * https://dev.max.ru/docs-api/methods/GET/messages
     * @api
     */
    public function getMessages(
        ?array $messageIds = null,
        ?int $chatId = null,
        ?int $from = null,
        ?int $to = null,
        int $count = 50
    ): MessageList {
        if ($messageIds !== null) {
            static::validateArray('messageIds', $messageIds, minItems: 1);

            $params = ['message_ids' => implode(',', array_unique($messageIds))];
        } else {
            static::validateNotNull('chatId', $chatId);

            $params = ['chat_id' => $chatId];
            if ($from !== null) {
                $params['from'] = $from;
            }
            if ($to !== null) {
                $params['to'] = $to;
            }
            $params['count'] = $count;

            if ($from !== null && $to !== null) {
                static::validateMustBeLess('to', 'from', $to < $from);
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
     * @api
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
     * @param int<1, 100> $count Максимальное количество сообщений в ответе (минимум: 1, максимум: 100).
     * @return MessageList Возвращает список сообщений.
     * @api
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
     * Получение информации о боте.
     *
     * Метод возвращает информацию о боте, который идентифицируется с помощью токена доступа `access_token`.
     * В ответе приходит объект User с вариантом наследования BotInfo, который содержит идентификатор бота,
     * его название, никнейм, время последней активности, описание и аватар (при наличии).
     *
     * @return BotInfo Информация о боте.
     * @link https://dev.max.ru/docs-api/methods/GET/me
     * @api
     */
    public function getMyInfo(): BotInfo
    {
        $data = $this->httpClient->get('/me');

        return BotInfo::newFromData($data);
    }

    /**
     * Получение закреплённого сообщения в групповом чате.
     *
     * Возвращает закреплённое сообщение в групповом чате.
     *
     * @param int $chatId ID чата.
     * @return GetPinnedMessageResult Закреплённое сообщение.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/pin
     * @api
     */
    public function getPinnedMessage(int $chatId): GetPinnedMessageResult
    {
        $data = $this->httpClient->get("/chats/$chatId/pin");

        return GetPinnedMessageResult::newFromData($data);
    }

    /**
     * Получение подписок.
     *
     * Если ваш бот получает данные через Webhook, этот метод возвращает список всех подписок.
     * При настройке уведомлений для production-окружения рекомендуем использовать Webhook.
     *
     * > Обратите внимание: для отправки вебхуков поддерживается только протокол HTTPS,
     * > включая самоподписанные сертификаты. HTTP не поддерживается
     *
     * @return GetSubscriptionsResult Список подписок.
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     * @api
     */
    public function getSubscriptions(): GetSubscriptionsResult
    {
        $data = $this->httpClient->get('/subscriptions');

        return GetSubscriptionsResult::newFromData($data);
    }

    /**
     * Получение обновлений.
     *
     * Этот метод можно использовать для получения обновлений при разработке и тестировании,
     * если ваш бот не подписан на Webhook. Для production-окружения рекомендуем использовать Webhook.
     *
     * Метод использует долгий опрос (long polling). Каждое обновление имеет свой номер последовательности.
     * Свойство `marker` в ответе указывает на следующее ожидаемое обновление.
     *
     * Все предыдущие обновления считаются завершёнными после прохождения параметра `marker`.
     * Если параметр `marker` **не передан**, бот получит все обновления, произошедшие после последнего подтверждения.
     *
     * @param int<1, 1000> $limit Максимальное количество обновлений для получения (минимум: 1, максимум: 1000).
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса (минимум: 0, максимум: 90).
     * @param int|null $marker Если передан, бот получит обновления, которые еще не были получены.
     * @param array<UpdateType|string>|null $types Список типов обновлений, которые бот хочет получить.
     * @return UpdateList Список обновлений.
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     * @api
     */
    public function getUpdates(
        int $limit = 100,
        int $timeout = 30,
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

        $data = $this->httpClient->get('/updates', $params);

        return UpdateList::newFromData($data);
    }

    /**
     * Загрузка файлов.
     *
     * Возвращает URL для последующей загрузки файла.
     *
     * Поддерживаются два типа загрузки:
     * - **Multipart upload** — более простой, но менее надёжный способ. В этом случае используется
     *   заголовок `Content-Type: multipart/form-data`. Файл отправляется целиком одним запросом.
     *   Если загрузка прервётся, невозможно её возобновить — придётся начать заново.
     * - **Resumable upload** — более надёжный способ, если заголовок `Content-Type` не равен
     *   `multipart/form-data`. Этот способ позволяет загружать файл частями и возобновить загрузку
     *   с последней успешно загруженной части в случае ошибок.
     *
     * Общие ограничения для обоих типов загрузки:
     * - Максимальный размер файла: 4 ГБ
     * - Можно загружать только один файл за раз
     *
     * @param UploadType $type Тип загружаемого файла. Возможные значения: `"image"`, `"video"`, `"audio"`, `"file"`.
     * @return UploadEndpoint Возвращает URL для загрузки вложения.
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     * @api
     */
    public function getUploadUrl(UploadType $type): UploadEndpoint
    {
        $data = $this->httpClient->post('/uploads', null, ['type' => $type->value]);

        return UploadEndpoint::newFromData($data);
    }

    /**
     * Получить информацию о видео.
     *
     * Возвращает подробную информацию о прикреплённом видео: URL-адреса воспроизведения и дополнительные метаданные.
     *
     * @param non-empty-string $videoToken Токен видео-вложения (шаблон: '[A-Za-z0-9_\\-]+').
     * @return VideoAttachmentDetails Подробная информация о видео.
     * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-
     * @api
     */
    public function getVideoAttachmentDetails(string $videoToken): VideoAttachmentDetails
    {
        if (!preg_match('/^[A-Za-z0-9_\\\\-]+$/', $videoToken)) {
            throw new MatchException('videoToken');
        }

        $data = $this->httpClient->get("/videos/$videoToken");

        return VideoAttachmentDetails::newFromData($data);
    }

    /**
     * Удаление бота из группового чата.
     *
     * Удаляет бота из участников группового чата.
     *
     * @param int $chatId ID чата.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/me
     * @api
     */
    public function leaveChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/me");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Закрепление сообщения в групповом чате.
     *
     * Закрепляет сообщение в групповом чате.
     *
     * @param int $chatId ID чата, где должно быть закреплено сообщение.
     * @param non-empty-string|PinMessageBody|RawModel $pinMessage Сообщение для закрепления.
     * @link https://dev.max.ru/docs-api/methods/PUT/chats/-chatId-/pin
     * @api
     */
    public function pinMessage(int $chatId, string|PinMessageBody|RawModel $pinMessage): void
    {
        if (is_string($pinMessage)) {
            $pinMessage = new PinMessageBody($pinMessage);
        }

        $data = $this->httpClient->put("/chats/$chatId/pin", $pinMessage->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаление участника из группового чата.
     *
     * Удаляет участника из группового чата. Для этого могут потребоваться дополнительные права.
     *
     * @param int $chatId ID чата.
     * @param int $userId Идентификатор пользователя для удаления из чата.
     * @param bool $block Если установлено в `true`, пользователь будет заблокирован в чате.
     *     Применяется только для чатов с публичной или приватной ссылкой. Игнорируется в остальных случаях.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members
     * @api
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
     * Отправка действия бота в групповой чат.
     *
     * Позволяет отправлять в групповой чат такие действия бота, как например: «набор текста» или «отправка фото».
     *
     * @param int $chatId ID чата.
     * @param SenderAction|ActionRequestBody|RawModel $action Действие бота.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/actions
     * @api
     */
    public function sendAction(int $chatId, SenderAction|ActionRequestBody|RawModel $action): void
    {
        if ($action instanceof SenderAction) {
            $action = new ActionRequestBody($action);
        }

        $data = $this->httpClient->post("/chats/$chatId/actions", $action->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отправить сообщение.
     *
     * Отправляет сообщение в чат. В результате метода возвращается идентификатор нового сообщения.
     *
     * @param int|null $userId Если вы хотите отправить сообщение пользователю, укажите его ID.
     * @param int|null $chatId Если сообщение отправляется в чат, укажите его ID.
     * @param NewMessageBody|RawModel|null $message Тело нового сообщения.
     * @psalm-param NewMessageBody|RawModel $message
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Возвращает информацию о созданном сообщении.
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     * @api
     */
    public function sendMessage(
        ?int $userId = null,
        ?int $chatId = null,
        NewMessageBody|RawModel|null $message = null,
        bool $disableLinkPreview = false
    ): SendMessageResult {
        $params = [];
        if ($userId !== null && $chatId !== null) {
            throw new ActionProhibited();
        }
        if ($chatId !== null) {
            $params['chat_id'] = $chatId;
        } else {
            static::validateNotNull('user_id', $userId);

            $params['user_id'] = $userId;
        }
        static::validateNotNull('message', $message);
        if ($disableLinkPreview) {
            $params['disable_link_preview'] = 'true';
        }

        $data = $this->httpClient->post('/messages', $message->jsonSerialize(), $params);

        return SendMessageResult::newFromData($data);
    }

    /**
     * Отправить сообщение в чат.
     *
     * Отправляет сообщение в чат. В результате метода возвращается идентификатор нового сообщения.
     *
     * @param int $chatId ID чата.
     * @param NewMessageBody|RawModel $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Возвращает информацию о созданном сообщении.
     * @api
     */
    public function sendMessageToChat(
        int $chatId,
        NewMessageBody|RawModel $message,
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
     * @param NewMessageBody|RawModel $message Тело нового сообщения.
     * @param bool $disableLinkPreview Если `false`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     * @return SendMessageResult Возвращает информацию о созданном сообщении.
     * @api
     */
    public function sendMessageToUser(
        int $userId,
        NewMessageBody|RawModel $message,
        bool $disableLinkPreview = false
    ): SendMessageResult {
        return $this->sendMessage($userId, null, $message, $disableLinkPreview);
    }

    /**
     * Назначить администратора группового чата.
     *
     * Назначает администраторов в групповой чат.
     * Возвращает значение `true`, если в групповой чат добавлены все администраторы.
     *
     * @param int $chatId ID чата.
     * @param ChatAdmin[]|ChatAdminsList|RawModel $admins Список администраторов.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/admins
     * @api
     */
    public function setAdmins(int $chatId, array|ChatAdminsList|RawModel $admins): void
    {
        if (is_array($admins)) {
            $admins = new ChatAdminsList($admins);
        }

        $data = $this->httpClient->post("/chats/$chatId/members/admins", $admins->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Подписка на обновления.
     *
     * Метод настраивает доставку событий бота через **Webhook** — основной механизм получения событий
     * в продуктовых интеграциях. При активной подписке **Long Polling** не работает.
     *
     * После вызова метода события отправляются на указанный Webhook-endpoint в виде HTTPS POST-запросов
     * с объектом {@see Update}.
     *
     * > Webhook-endpoint должен возвращать **HTTP 200** в течение 30 секунд.
     * > Любой другой код ответа или превышение тайм-аута — ошибка доставки
     *
     * @param SubscriptionRequestBody|RawModel $subscription Параметры подписки.
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     * @api
     */
    public function subscribe(SubscriptionRequestBody|RawModel $subscription): void
    {
        $data = $this->httpClient->post('/subscriptions', $subscription->jsonSerialize());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаление закреплённого сообщения в групповом чате.
     *
     * Удаляет закреплённое сообщение в групповом чате.
     *
     * @param int $chatId ID чата, из которого нужно удалить закреплённое сообщение.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/pin
     * @api
     */
    public function unpinMessage(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/pin");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отписка от обновлений.
     *
     * Отписывает бота от получения обновлений через Webhook. После вызова этого метода бот перестаёт получать
     * уведомления о новых событиях, и становится доступна доставка уведомлений через API с длительным опросом.
     *
     * @param non-empty-string $url URL, который нужно удалить из подписок на WebHook.
     * @https://dev.max.ru/docs-api/methods/DELETE/subscriptions
     * @api
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
