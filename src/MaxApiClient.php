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
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Enum\UploadType;
use MaxMessenger\Bot\Model\Request\ActionRequestBody;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;
use MaxMessenger\Bot\Model\Request\BotPatch;
use MaxMessenger\Bot\Model\Request\CallbackAnswer;
use MaxMessenger\Bot\Model\Request\ChatAdmin;
use MaxMessenger\Bot\Model\Request\ChatAdminsList;
use MaxMessenger\Bot\Model\Request\ChatButton;
use MaxMessenger\Bot\Model\Request\ChatPatch;
use MaxMessenger\Bot\Model\Request\NewCommentBody;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Bot\Model\Request\PinMessageBody;
use MaxMessenger\Bot\Model\Request\RawModel;
use MaxMessenger\Bot\Model\Request\SubscriptionRequestBody;
use MaxMessenger\Bot\Model\Request\UserIdsList;
use MaxMessenger\Bot\Model\Request\ValidateTrait;
use MaxMessenger\Bot\Model\Response\BotCommandsInfo;
use MaxMessenger\Bot\Model\Response\BotInfo;
use MaxMessenger\Bot\Model\Response\Callback;
use MaxMessenger\Bot\Model\Response\Chat;
use MaxMessenger\Bot\Model\Response\ChatList;
use MaxMessenger\Bot\Model\Response\ChatMember;
use MaxMessenger\Bot\Model\Response\ChatMembersList;
use MaxMessenger\Bot\Model\Response\CommentMessage;
use MaxMessenger\Bot\Model\Response\CommentMessageBody;
use MaxMessenger\Bot\Model\Response\CommentMessageList;
use MaxMessenger\Bot\Model\Response\ContactAttachmentPayload;
use MaxMessenger\Bot\Model\Response\GetPinnedMessageResult;
use MaxMessenger\Bot\Model\Response\GetSubscriptionsResult;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageBody;
use MaxMessenger\Bot\Model\Response\MessageList;
use MaxMessenger\Bot\Model\Response\ModifyMembersResult;
use MaxMessenger\Bot\Model\Response\SendCommentResult;
use MaxMessenger\Bot\Model\Response\SendMessageResult;
use MaxMessenger\Bot\Model\Response\SimpleQueryResult;
use MaxMessenger\Bot\Model\Response\Update;
use MaxMessenger\Bot\Model\Response\UpdateList;
use MaxMessenger\Bot\Model\Response\UploadEndpoint;
use MaxMessenger\Bot\Model\Response\VideoAttachmentDetails;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientException;
use SensitiveParameter;

use function array_filter;
use function array_shift;
use function array_unique;
use function hash_equals;
use function hash_hmac;
use function implode;
use function is_array;
use function is_string;
use function usleep;

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
     * Добавляет участников в групповой чат.
     *
     * Бот должен быть администратором этого чата с правом `add_remove_members`.
     *
     * Добавить подписчиков в канал с помощью этого метода нельзя.
     *
     * @param int $chatId ID группового чата.
     * @param UserIdsList|RawModel|non-empty-array<int> $userIds Список ID пользователей для добавления
     *     в групповой чат. При использованнии массива, смотрите ограничения в {@see UserIdsList}.
     * @return ModifyMembersResult Результат запроса на изменение списка участников чата.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members
     * @deprecated С 9 сентября 2026 г. работа метода ограничена, а с 30 сентября 2026 г. он будет удалён.
     *     После этого API MAX не предоставляет готовой возможности для добавления участников в групповой чат.
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
     * Отправляет ответ после того, как пользователь нажал на кнопку. Ответом является обновлённое сообщение.
     *
     * Ограничения: можно отправлять не более двух ответов в секунду в один диалог, групповой чат или канал.
     * При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.
     *
     * @param non-empty-string|Callback $callbackId Идентификатор кнопки, на которую нажал пользователь
     *     (pattern: '^[\x21-\x7E]+$'). Бот получает идентификатор как часть {@see Update} с типом `message_callback`.
     *     Пример получения идентификатора: `$update->getCallback()->getCallbackId()`.
     * @param CallbackAnswer|RawModel|NewMessageBody $answer Ответ на callback: обновленное сообщение и/или уведомление.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения
     *     или поста.
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function answerOnCallback(
        string|Callback $callbackId,
        NewMessageBody|RawModel|CallbackAnswer $answer,
        bool $disableLinkPreview = false,
    ): void {
        if ($callbackId instanceof Callback) {
            $callbackId = $callbackId->getCallbackId();
        }

        self::validateString('callbackId', $callbackId, minLength: 1, pattern: '/^[\x21-\x7E]+$/');

        if ($answer instanceof NewMessageBody) {
            $answer = new CallbackAnswer($answer);
        }

        $params = ['callback_id' => $callbackId];
        if ($disableLinkPreview) {
            $params['disable_link_preview'] = 'true';
        }

        $data = $this->httpClient->post('/answers', $answer->jsonSerialize(), $params);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Отменяет права администратора в групповом чате или канале.
     *
     * Лишает пользователя или бота прав администратора в групповом чате или канале.
     * При этом из чата и канала они не исключаются.
     *
     * Бот должен быть администратором этого чата или канала с правом `add_admins`.
     *
     * Alias: {@see deleteAdmins}
     *
     * @param int $chatId ID группового чата или канала.
     * @param int $userId Идентификатор пользователя или бота, которого надо лишить прав администратора.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-
     */
    public function deleteAdmin(int $chatId, int $userId): void
    {
        $this->deleteAdmins($chatId, $userId);
    }

    /**
     * Отменяет права администратора в групповом чате или канале.
     *
     * Лишает пользователя или бота прав администратора в групповом чате или канале.
     * При этом из чата и канала они не исключаются.
     *
     * Бот должен быть администратором этого чата или канала с правом `add_admins`.
     *
     * @param int $chatId ID группового чата или канала.
     * @param int $userId Идентификатор пользователя или бота, которого надо лишить прав администратора.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-
     */
    public function deleteAdmins(int $chatId, int $userId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/admins/$userId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет групповой чат.
     *
     * Удаляет групповой чат для всех участников.
     *
     * Удалить чат может только его владелец: у бота это чаты, созданные пользователем по кнопке
     * {@see ChatButton} в сообщении бота. В остальных случаях, даже с правами администратора, сервер
     * отвечает ошибкой `Insufficient access rights to perform this action`.
     *
     * @param int $chatId ID чата.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-
     * @deprecated Метод удалён из официальной схемы API. На 15 сентября 2026 г. он остаётся рабочим,
     *     но может быть отключён в любой момент.
     */
    public function deleteChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет комментарий к посту в канале.
     *
     * Удаляет комментарий пользователя или бота. Можно удалять как свои комментарии, так и чужие.
     * Восстановить удалённый комментарий нельзя.
     *
     * Если канал архивирован или в нём отключены комментарии, старые комментарии по-прежнему можно удалять.
     *
     * Бот должен быть администратором этого канала с правами `read_all_messages` и `delete`.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), комментарий к которому нужно
     *     удалить (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-string|CommentMessage|CommentMessageBody $commentId Идентификатор удаляемого комментария
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see CommentMessage} или {@see CommentMessageBody}.
     * @link https://dev.max.ru/docs-api/methods/DELETE/messages/-messageId-/comments
     */
    public function deleteComment(
        string|Message|MessageBody $messageId,
        string|CommentMessage|CommentMessageBody $commentId,
    ): void {
        $messageId = self::extractMid($messageId, 'messageId');
        $commentId = self::extractMid($commentId, 'commentId');

        $data = $this->httpClient->delete("/messages/$messageId/comments", ['comment_id' => $commentId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет сообщение в чате.
     *
     * Удаляет сообщения, если бот является администратором и имеет право на удаление:
     * - В канале — любые сообщения.
     * - В диалоге — только сообщения, отправленные самим ботом.
     * - В групповом чате — любые сообщения.
     *
     * Можно удалять не более двух сообщений в секунду в одном диалоге, групповом чате или канале.
     * При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед удалением.
     *
     * @param non-empty-string|Message|MessageBody $messageId ID удаляемого сообщения
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message} или {@see MessageBody}.
     * @link https://dev.max.ru/docs-api/methods/DELETE/messages
     */
    public function deleteMessage(string|Message|MessageBody $messageId): void
    {
        $messageId = self::extractMid($messageId, 'messageId');

        $data = $this->httpClient->delete('/messages', ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Редактирует информацию о групповом чате или канале.
     *
     * Редактирует информацию о групповом чате или канале, включая название, описание,
     * иконку и закреплённое сообщение или пост.
     *
     * Бот должен быть администратором этого чата или канала.
     *
     * @param int $chatId ID чата или канала.
     * @param ChatPatch|RawModel $chatPatch Данные для редактирования чата.
     * @return Chat Обновлённый объект чата или канала.
     * @link https://dev.max.ru/docs-api/methods/PATCH/chats/-chatId-
     */
    public function editChat(int $chatId, ChatPatch|RawModel $chatPatch): Chat
    {
        $data = $this->httpClient->patch("/chats/$chatId", $chatPatch->jsonSerialize());

        return Chat::newFromData($data);
    }

    /**
     * Редактирует комментарий к посту в канале.
     *
     * Бот должен быть участником этого канала. Комментарии, опубликованные от имени канала, можно редактировать, только
     * если боту назначено право администратора `edit`; без этого права бот может редактировать лишь свои комментарии.
     *
     * Если канал архивирован или в нём отключены комментарии, бот может редактировать старые комментарии.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), комментарий к которому
     *     нужно отредактировать (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-string|CommentMessage|CommentMessageBody $commentId Идентификатор редактируемого
     *     комментария (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see CommentMessage} или {@see CommentMessageBody}.
     * @param NewCommentBody|RawModel|non-empty-string $commentBody Тело нового комментария.
     * @link https://dev.max.ru/docs-api/methods/PUT/messages/-messageId-/comments
     */
    public function editComment(
        string|Message|MessageBody $messageId,
        string|CommentMessage|CommentMessageBody $commentId,
        NewCommentBody|RawModel|string $commentBody,
    ): void {
        $messageId = self::extractMid($messageId, 'messageId');
        $commentId = self::extractMid($commentId, 'commentId');

        if (is_string($commentBody)) {
            $commentBody = new NewCommentBody($commentBody);
        }

        $data = $this->httpClient->put(
            "/messages/$messageId/comments",
            $commentBody->jsonSerialize(),
            ['comment_id' => $commentId],
        );

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Редактирует сообщение в чате.
     *
     * Редактирует сообщения и посты, отправленные ботом.
     *
     * Ограничения:
     * - Есть особенности редактирования сообщений в диалогах с ботом, связанные со сроком давности:
     *   - сообщения с кнопками `inline_keyboard` редактируются независимо от срока давности.
     *   - остальные сообщения редактируются, если они отправлены менее 7 суток назад.
     * - В групповых чатах и каналах любые сообщения редактируются независимо от срока давности.
     * - Можно редактировать не более двух сообщений в секунду в одном диалоге, групповом чате или канале.
     *   При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед редактированием.
     *
     * @param non-empty-string|Message|MessageBody $messageId ID редактируемого сообщения
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Тело нового сообщения.
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function editMessage(
        string|Message|MessageBody $messageId,
        NewMessageBody|RawModel|string $messageBody,
    ): void {
        $messageId = self::extractMid($messageId, 'messageId');

        if (is_string($messageBody)) {
            $messageBody = new NewMessageBody($messageBody);
        }

        $data = $this->httpClient->put('/messages', $messageBody->jsonSerialize(), ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Редактирует команды бота.
     *
     * Добавляет, изменяет или удаляет команды бота, отображаемые пользователю в качестве подсказок при вводе `/`.
     * Чтобы удалить команды, передайте пустой массив `commands`.
     *
     * @param BotCommandsPatch|RawModel $commands Данные для обновления команд бота (maxItems: 32).
     *     Чтобы удалить все команды, передайте пустой список команд.
     * @return BotCommandsInfo Информация о командах бота.
     * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands
     */
    public function editMyCommands(BotCommandsPatch|RawModel $commands): BotCommandsInfo
    {
        $data = $this->httpClient->patch('/me/commands', $commands->jsonSerialize());

        return BotCommandsInfo::newFromData($data);
    }

    /**
     * Редактирует информацию о боте.
     *
     * Редактирует информацию о текущем боте. Позволяет обновить имя, описание, команды и аватар бота.
     *
     * @param BotPatch|RawModel $botPatch Данные для редактирования информации о боте.
     * @return BotInfo Изменённая информация о боте.
     * @link https://dev.max.ru/docs-api/methods/PATCH/me
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
     * Бот должен быть администратором этого чата или канала.
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
     * Возвращает информацию о канале по его публичной ссылке.
     * Метод доступен только для каналов — получить информацию о чате по публичной ссылке не получится.
     *
     * @param non-empty-string $chatLink Публичная ссылка на канал (pattern: '^@?[a-zA-Z]+[\w-]*$').
     * @return Chat Информация о канале.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatLink-
     * @deprecated С 10 июля 2026 г. метод удалён из API и не работает: сервер отвечает `404 chat.not.found`.
     */
    public function getChatByLink(string $chatLink): Chat
    {
        self::validateString('chatLink', $chatLink, minLength: 1, pattern: '/^@?[a-zA-Z]+[\w-]*$/');

        $data = $this->httpClient->get("/chats/$chatLink");

        return Chat::newFromData($data);
    }

    /**
     * Получает список всех групповых чатов и каналов.
     *
     * Возвращает список групповых чатов и каналов, в которые добавлен бот,
     * информацию о каждом чате и маркер для перехода к следующей странице списка.
     *
     * @param int<1, 100> $count Количество запрашиваемых чатов.
     * @param int|null $marker Указатель на следующую страницу данных. Для первой страницы передайте `null`.
     *
     * @return ChatList В ответе с пагинацией возвращаются чаты.
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     * @deprecated Начиная с июня 2026 этот метод официально не поддерживается, и API не предоставляет готовой
     *     возможности для получения списка групповых чатов и каналов, в которые добавлен бот. На 15 сентября
     *     2026 г. метод остаётся рабочим и возвращает актуальные данные, но может быть отключён в любой момент.
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
     * Получает комментарий по ID.
     *
     * Возвращает информацию о комментарии к посту в канале по его идентификатору (`mid`).
     *
     * Бот должен быть администратором этого канала с правом `read_all_messages`.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относится
     *     комментарий (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-string|CommentMessage|CommentMessageBody $commentId Идентификатор комментария (`mid`)
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see CommentMessage} или {@see CommentMessageBody}.
     * @return CommentMessage Информация о комментарии, идентификатор которого был передан в запросе.
     * @link https://dev.max.ru/docs-api/methods/GET/messages/-messageId-/comments/-commentId-
     */
    public function getCommentById(
        string|Message|MessageBody $messageId,
        string|CommentMessage|CommentMessageBody $commentId,
    ): CommentMessage {
        $messageId = self::extractMid($messageId, 'messageId');
        $commentId = self::extractMid($commentId, 'commentId');

        $data = $this->httpClient->get("/messages/$messageId/comments/$commentId");

        return CommentMessage::newFromData($data);
    }

    /**
     * Получает комментарии к посту в канале.
     *
     * Возвращает комментарии к посту в канале. Можно указать промежуток времени, за который нужно получить комментарии,
     * и/или их количество. Возвращаются последние `$count` комментариев из указанного промежутка времени.
     *
     * Если указан `$commentIds`, возвращаются только запрошенные комментарии, а остальные параметры игнорируются.
     *
     * Бот должен быть администратором этого канала с правом `read_all_messages`.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относятся
     *     комментарии (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-array<non-empty-string>|null $commentIds Список ID комментариев, которые нужно получить
     *     (uniqueItems: true). Если параметр указан, остальные параметры игнорируются.
     * @param non-negative-int|null $before Время, до которого будут запрошены все комментарии с начала поста
     *     (Unix-время в миллисекундах).
     * @param non-negative-int|null $after Время, начиная с которого будут запрошены все комментарии до конца поста
     *     (Unix-время в миллисекундах).
     * @param int<1, 100> $count Максимальное количество комментариев в ответе.
     * @return CommentMessageList Список запрошенных комментариев.
     * @link https://dev.max.ru/docs-api/methods/GET/messages/-messageId-/comments
     */
    public function getComments(
        string|Message|MessageBody $messageId,
        ?array $commentIds = null,
        ?int $before = null,
        ?int $after = null,
        int $count = 50,
    ): CommentMessageList {
        $messageId = self::extractMid($messageId, 'messageId');

        if ($commentIds !== null) {
            self::validateArray('commentIds', $commentIds, minItems: 1);

            $params = ['comment_ids' => implode(',', array_unique($commentIds))];
        } else {
            self::validateInt('count', $count, 1, 100);

            $params = [];
            if ($before !== null) {
                self::validateInt('before', $before, 0);

                $params['before'] = $before;
            }
            if ($after !== null) {
                self::validateInt('after', $after, 0);

                $params['after'] = $after;
            }
            $params['count'] = $count;

            if ($before !== null && $after !== null) {
                self::validateMustBeLess('after', 'before', $after < $before);
            }
        }

        $data = $this->httpClient->get("/messages/$messageId/comments", $params);

        return CommentMessageList::newFromData($data);
    }

    /**
     * Получение комментариев по ID.
     *
     * Возвращает массив заданных комментариев к посту в канале. Можно указать один идентификатор или несколько.
     *
     * Бот должен быть администратором этого канала с правом `read_all_messages`.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относятся
     *     комментарии (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message}
     *     или {@see MessageBody} — идентификатор будет извлечён из него автоматически.
     * @param non-empty-array<non-empty-string> $commentIds Список ID комментариев, которые нужно получить
     *     (uniqueItems: true).
     * @return CommentMessageList Список запрошенных комментариев.
     */
    public function getCommentsById(
        string|Message|MessageBody $messageId,
        array $commentIds,
    ): CommentMessageList {
        return $this->getComments($messageId, $commentIds);
    }

    /**
     * Получение комментариев к посту в канале.
     *
     * Возвращает последние `$count` комментариев к указанному посту.
     * Можно указать промежуток времени, за который нужно получить комментарии.
     *
     * Бот должен быть администратором этого канала с правом `read_all_messages`.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относятся
     *     комментарии (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message}
     *     или {@see MessageBody} — идентификатор будет извлечён из него автоматически.
     * @param non-negative-int|null $before Время, до которого будут запрошены все комментарии с начала поста
     *     (Unix-время в миллисекундах).
     * @param non-negative-int|null $after Время, начиная с которого будут запрошены все комментарии до конца поста
     *     (Unix-время в миллисекундах).
     * @param int<1, 100> $count Максимальное количество комментариев в ответе.
     * @return CommentMessageList Список запрошенных комментариев.
     */
    public function getCommentsFromPost(
        string|Message|MessageBody $messageId,
        ?int $before = null,
        ?int $after = null,
        int $count = 50,
    ): CommentMessageList {
        return $this->getComments($messageId, null, $before, $after, $count);
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
     * Бот должен быть администратором этого чата или канала.
     *
     * @param int $chatId ID группового чата или канала.
     * @param non-empty-array<int>|null $userIds Список ID пользователей, чьё членство нужно получить
     *     (uniqueItems: true). Когда этот аргумент передан, аргументы `count` и `marker` игнорируются.
     * @param int|null $marker Указатель на следующую страницу данных.
     * @param int<1, 100> $count Максимальное количество участников в ответе.
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
     * Получает сообщение или пост по ID.
     *
     * Возвращает сообщение или пост из чата или канала по его ID.
     *
     * @param non-empty-string|Message|MessageBody $messageId ID сообщения (`mid`), чтобы получить одно сообщение
     *     в чате или канале (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @return Message Информация о сообщении или посте с ID, указанным в запросе.
     * @link https://dev.max.ru/docs-api/methods/GET/messages/-messageId-
     */
    public function getMessageById(string|Message|MessageBody $messageId): Message
    {
        $messageId = self::extractMid($messageId, 'messageId');

        $data = $this->httpClient->get("/messages/$messageId");

        return Message::newFromData($data);
    }

    /**
     * Получает список сообщений или постов.
     *
     * Возвращает массив сообщений из чата или постов из канала.
     *
     * Для выполнения запроса нужно указать один из параметров — `chat_id` или `message_ids`:
     *
     * - `chat_id` — ID чата или канала. Сообщения возвращаются в обратном порядке:
     *   последние сообщения будут первыми в массиве.
     * - `message_ids` — Список ID сообщений (`mid`). Можно указать один идентификатор или несколько.
     *
     * @param non-empty-array<non-empty-string>|null $messageIds Список ID сообщений или постов, которые нужно получить
     *     (uniqueItems: true). Обязательный параметр, если не указан `chatId`.
     * @param int|null $chatId ID чата или канала. Обязательный параметр, если не указан `messageIds`.
     * @param non-negative-int|null $from Время, до которого будут запрошены все сообщения или посты, начиная
     *     с первого опубликованного (Unix-время в миллисекундах).
     * @param non-negative-int|null $to Время, начиная с которого будут запрошены все сообщения или посты, — вплоть
     *     до последнего опубликованного (Unix-время в миллисекундах).
     * @param int<1, 100> $count Максимальное количество сообщений или постов в ответе.
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
     * Получение сообщений или постов по ID.
     *
     * Возвращает массив заданных сообщений или постов. Можно указать один идентификатор или несколько.
     *
     * @param non-empty-array<non-empty-string> $messageIds Список ID сообщений или постов, которые нужно получить
     *     (uniqueItems: true).
     * @return MessageList Возвращает список сообщений или постов.
     */
    public function getMessagesById(array $messageIds): MessageList
    {
        return $this->getMessages($messageIds);
    }

    /**
     * Получение сообщений из чата или постов из канала.
     *
     * Возвращает массив сообщений из указанного чата или постов из указанного канала.
     * Сообщения и посты возвращаются в обратном порядке: последние сообщения и посты будут первыми в массиве.
     *
     * @param int $chatId ID чата или канала.
     * @param non-negative-int|null $from Время, до которого будут запрошены все сообщения или посты, начиная
     *     с первого опубликованного (Unix-время в миллисекундах).
     * @param non-negative-int|null $to Время, начиная с которого будут запрошены все сообщения или посты, — вплоть
     *     до последнего опубликованного (Unix-время в миллисекундах).
     * @param int<1, 100> $count Максимальное количество сообщений или постов в ответе.
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
     * Получает закреплённое сообщение в групповом чате или пост в канале.
     *
     * Бот должен быть администратором этого чата или канала.
     *
     * @param int $chatId ID чата или канала.
     * @return GetPinnedMessageResult Закреплённое сообщение или пост.
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
     * @param int<1, 1000> $limit Максимальное количество событий для получения.
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса.
     * @param int|null $marker Маркер для получения событий с конкретной позиции.
     *     Для получения всех ранее непрочитанных событий, передайте `null`.
     * @param array<UpdateType|string>|null $types Список типов событий, которые вы хотите получать
     *     (uniqueItems: true). Полный список возможных событий смотрите в описании класса {@see Update}.
     * @return UpdateList Список обновлений событий с указателем на следующую страницу данных.
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function getUpdates(
        int $limit = 100,
        int $timeout = 60,
        ?int $marker = null,
        ?array $types = null,
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
     * Получает URL для загрузки медиафайлов.
     *
     * Возвращает URL для последующей загрузки медиафайла.
     *
     * Медиафайл может быть одного из типов:
     *
     * - `image` — изображения (JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC).
     * - `video` — видеофайлы (MP4, MOV, MKV, WEBM).
     * - `audio` — аудиофайлы (MP3, WAV, M4A и другие).
     * - `file` — файлы в распространённых форматах (например, TXT, DOC и другие).
     *
     * Ограничения при загрузке медиафайлов:
     *
     * - Максимальный размер одного медиафайла, который можно загрузить, зависит от его типа:
     *   - 50 МБ или не более 7680 × 7680 px — для изображений (должны выполняться оба критерия).
     *   - 250 МБ — для видео.
     *   - 256 МБ или длительностью не более 60 мин — для аудио (должны выполняться оба критерия).
     *   - 4 ГБ — для остальных файлов.
     * - По URL-ссылке, которая вернётся в ответ на запрос, можно загрузить только один файл. Если вы хотите загрузить
     *   ещё файл, отправьте запрос повторно и используйте новую URL-ссылку
     *
     * @param UploadType $type Тип загружаемого медиафайла.
     * @return UploadEndpoint Возвращает URL для загрузки вложения и токен для загрузки медиафайла.
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function getUploadUrl(UploadType $type): UploadEndpoint
    {
        $data = $this->httpClient->post('/uploads', null, ['type' => $type->value]);

        return UploadEndpoint::newFromData($data);
    }

    /**
     * Получает информацию о видео, прикреплённом к сообщению.
     *
     * Возвращает подробную информацию о видео, прикреплённом к сообщению в чате или канале:
     * URL-адреса воспроизведения и дополнительные метаданные.
     *
     * @param non-empty-string $videoToken Токен видео-вложения (pattern: '^vid\.[\x21-\x7E]+$').
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
     * Закрепляет сообщение в групповом чате или пост в канале.
     *
     * Бот должен быть администратором этого чата или канала.
     *
     * @param int $chatId ID группового чата или канала, где нужно закрепить сообщение или пост.
     * @param PinMessageBody|RawModel|non-empty-string $pinMessage Сообщение или пост для закрепления.
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
     * Бот должен быть администратором этого чата или канала с правом `add_admins`.
     *
     * Права, которые можно назначить, зависят от того, где (канал или чат) и кому (пользователь или бот) выданы.
     * Если вы хотите изменить назначенные права, вызовите повторно текущий метод с обновлённым списком прав.
     * Полный список доступных прав администратора и условия их назначения описаны классе {@see ChatAdminPermission}.
     *
     * **Описание доступных прав администратора**:
     *
     * - `read_all_messages` — право читать все сообщения в групповом чате или посты в канале. Без этого права не
     *   получится управлять сообщениями: закреплять (`pin_message`), редактировать и удалять посты в каналах
     *   (`edit` и `delete`) и групповых чатах (`write`). Это право важно при назначении ботов: без него бот не будет
     *   получать события группового чата или канала. Управление `read_all_messages` в интерфейсе мессенджера доступно
     *   только для ботов в групповых чатах.
     * - `edit` — право редактировать посты и комментарии в каналах (для групповых чатов недоступно). Право можно
     *   назначить, только если уже есть право `read_all_messages` или вместе с ним. Ранее вместо `edit` в API
     *   использовалось `edit_message` — в ответе могут возвращаться оба значения, однако при назначении новых прав
     *   администраторов используйте `edit`. Управление `edit` также дублируется в интерфейсе мессенджера.
     * - `delete` — право удалять посты и комментарии в каналах (для групповых чатов недоступно). Право можно назначить,
     *   только если уже есть право `read_all_messages` или вместе с ним. Ранее вместо `delete` в API использовалось
     *   `delete_message` — в ответе могут возвращаться оба значения, однако при назначении новых прав администраторов
     *   используйте `delete`. Управление `delete` также дублируется в интерфейсе мессенджера.
     * - `write` — право редактировать и удалять сообщения в групповых чатах, а также писать посты и комментарии в
     *   каналах. Право можно назначить, только если уже есть право `read_all_messages` или вместе с ним. Ранее вместо
     *   `write` в API использовалось `post_edit_delete_message` — в ответе могут возвращаться оба значения, однако при
     *   назначении новых прав администраторам используйте `write`. Управление `write` также дублируется в интерфейсе
     *   мессенджера.
     * - `pin_message` — право закреплять сообщение. Право можно назначить, только если уже есть право
     *   `read_all_messages` или вместе с ним.
     * - `change_chat_info` — право изменять информацию о канале или групповом чате.
     * - `add_remove_members` — право добавлять и удалять участников группового чата или подписчиков канала. Для
     *   пользователей право доступно и в чатах, и в каналах, для ботов — только в чатах. Управление
     *   `add_remove_members` также дублируется в интерфейсе мессенджера.
     * - `add_admins` — право добавлять и удалять администраторов группового чата или канала. Управление `add_admins`
     *   дублируется в интерфейсе мессенджера.
     * - `edit_link` — право изменять ссылку на групповой чат (для каналов недоступно). Управление `edit_link` также
     *   дублируется в интерфейсе мессенджера.
     * - `can_call` — право звонить в групповом чате (для каналов недоступно). Право проставляется автоматически при
     *   назначении администратором. Управление `can_call` в интерфейсе мессенджера не дублируется.
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
     * Бот должен быть администратором этого чата или канала с правом `add_remove_members`.
     *
     * @param int $chatId ID группового чата или канала.
     * @param int $userId ID пользователя, которого нужно удалить из группового чата или канала.
     * @param bool $block Если передать `true`, пользователь будет заблокирован в чате. Применяется
     *     только для чатов с публичной или приватной ссылкой. Игнорируется в остальных случаях.
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
     * Отправляет действие бота в диалог или групповой чат.
     *
     * Отправляет в диалог или групповой чат такие действия бота, как например: «набор текста» или «отправка фото».
     *
     * Для каналов сервер принимает запрос, но участникам действие не показывается.
     *
     * @param int $chatId ID диалога или группового чата.
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
     * Отправляет комментарий к посту в канале.
     *
     * Возвращает созданный комментарий.
     *
     * Для отправки комментария в настройках канала должны быть включены комментарии, а бот должен быть
     * администратором этого канала с правами `read_all_messages` и `write`.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относится
     *     комментарий (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param NewCommentBody|RawModel|non-empty-string $commentBody Тело нового комментария.
     * @return SendCommentResult Информация о созданном комментарии.
     * @link https://dev.max.ru/docs-api/methods/POST/messages/-messageId-/comments
     */
    public function sendComment(
        string|Message|MessageBody $messageId,
        NewCommentBody|RawModel|string $commentBody,
    ): SendCommentResult {
        $messageId = self::extractMid($messageId, 'messageId');

        if (is_string($commentBody)) {
            $commentBody = new NewCommentBody($commentBody);
        }

        $data = $this->httpClient->post("/messages/$messageId/comments", $commentBody->jsonSerialize());

        return SendCommentResult::newFromData($data);
    }

    /**
     * Отправляет сообщение в диалог, групповой чат или канал.
     *
     * Возвращает созданное сообщение.
     *
     * Ограничения: можно отправлять не более двух сообщений в секунду в один диалог, групповой чат или канал.
     * При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.
     *
     * @param int|null $userId Если вы хотите отправить сообщение пользователю, укажите его ID.
     * @param int|null $chatId Если сообщение отправляется в чат, укажите его ID.
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function sendMessage(
        ?int $userId,
        ?int $chatId,
        NewMessageBody|RawModel|string $messageBody,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        $params = [];
        if ($userId !== null && $chatId !== null) {
            throw new RequiredArgumentsException('Only one of the arguments (userId, chatId) must be non-null.');
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

        if (is_string($messageBody)) {
            $messageBody = new NewMessageBody($messageBody);
        }

        $retryAttempts = $this->retryAttempts ?? $this->config->getRetryAttempts();
        do {
            try {
                $data = $this->httpClient->post('/messages', $messageBody->jsonSerialize(), $params);

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
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendMessageToChat(
        int $chatId,
        NewMessageBody|RawModel|string $messageBody,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->sendMessage(null, $chatId, $messageBody, $disableLinkPreview);
    }

    /**
     * Отправить сообщение пользователю.
     *
     * Отправляет сообщение в диалог. В результате метода возвращается идентификатор нового сообщения.
     *
     * @param int $userId ID пользователя.
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Тело нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     */
    public function sendMessageToUser(
        int $userId,
        NewMessageBody|RawModel|string $messageBody,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->sendMessage($userId, null, $messageBody, $disableLinkPreview);
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
     * Открепляет сообщение в групповом чате или пост в канале.
     *
     * Бот должен быть администратором этого чата или канала.
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

        return hash_equals($validHash, $hash);
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
     * @return list<string|int>
     */
    private function convertEnumsToStrings(array $arr): array
    {
        $result = [];
        foreach ($arr as $item) {
            $result[] = $item instanceof BackedEnum ? $item->value : $item;
        }

        return $result;
    }

    /**
     * Извлекает и проверяет идентификатор сообщения, поста или комментария.
     *
     * @param non-empty-string|Message|MessageBody|CommentMessage|CommentMessageBody $id Идентификатор (`mid`)
     *     или объект, из которого он будет извлечён.
     * @param non-empty-string $argumentName Имя аргумента для сообщения об ошибке валидации.
     * @return non-empty-string
     */
    private static function extractMid(
        string|Message|MessageBody|CommentMessage|CommentMessageBody $id,
        string $argumentName,
    ): string {
        if ($id instanceof Message || $id instanceof CommentMessage) {
            $id = $id->getBody()->getMid();
        } elseif ($id instanceof MessageBody || $id instanceof CommentMessageBody) {
            $id = $id->getMid();
        }

        self::validateString($argumentName, $id, minLength: 1, pattern: '/^mid\.[a-zA-Z0-9_\-]+$/');

        return $id;
    }
}
