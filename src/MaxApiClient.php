<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use Closure;
use MaxMessenger\Bot\Contract\MaxApiConfigInterface;
use MaxMessenger\Bot\Contract\MaxHttpClientInterface;
use MaxMessenger\Bot\Exception\ModifyMembersError;
use MaxMessenger\Bot\Exception\RequiredArgumentsException;
use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\ForbiddenException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotAllowedException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\NotFoundException;
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\UnprocessableEntityException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Enum\UpdateType;
use MaxMessenger\Bot\Model\Enum\UploadType;
use MaxMessenger\Bot\Model\Request\ActionRequestBody;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;
use MaxMessenger\Bot\Model\Request\CallbackAnswer;
use MaxMessenger\Bot\Model\Request\ChatAdmin;
use MaxMessenger\Bot\Model\Request\ChatAdminsList;
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
use SensitiveParameter;

use function array_filter;
use function array_map;
use function array_shift;
use function array_unique;
use function base64_encode;
use function bin2hex;
use function hash_equals;
use function hash_hmac;
use function implode;
use function is_array;
use function is_string;
use function rawurlencode;
use function rtrim;
use function str_replace;
use function strtolower;
use function strtr;
use function usleep;

/**
 * API Max клиент.
 *
 * @link https://dev.max.ru/docs-api/
 */
final class MaxApiClient
{
    use ValidateTrait;

    private readonly MaxApiConfigInterface $config;
    /**
     * @var Closure(non-empty-string $method, TemporaryExceptionInterface|BadRequestException $exception): void|null
     */
    private ?Closure $exceptionLogger = null;
    private readonly MaxHttpClientInterface $httpClient;

    /**
     * Создаёт клиент с токеном доступа или готовыми настройками подключения.
     *
     * @param non-empty-string|MaxApiConfigInterface $accessTokenOrConfig Токен доступа либо настройки API.
     * @param bool $useRussianTrustedCaCertificates Доверять корневым сертификатам Минцифры, поставляемым с пакетом
     *     (см. {@see MaxApiConfig::useRussianTrustedCaCertificates()}). Применяется, только если передан токен.
     * @param MaxHttpClientInterface|null $httpClient Клиент запросов или `null` для стандартного клиента.
     */
    public function __construct(
        #[SensitiveParameter]
        string|MaxApiConfigInterface $accessTokenOrConfig,
        bool $useRussianTrustedCaCertificates = false,
        ?MaxHttpClientInterface $httpClient = null,
    ) {
        $this->config = is_string($accessTokenOrConfig)
            ? new MaxApiConfig($accessTokenOrConfig, $useRussianTrustedCaCertificates)
            : $accessTokenOrConfig;

        $this->httpClient = $httpClient ?? new MaxHttpClient($this->config);
    }

    /**
     * Добавляет участников в групповой чат.
     *
     * Могут потребоваться дополнительные права.
     *
     * Добавить подписчиков в канал с помощью этого метода нельзя.
     *
     * @param int $chatId Идентификатор группового чата.
     * @param UserIdsList|RawModel|non-empty-array<int> $userIds Список идентификаторов пользователей для добавления
     *     в групповой чат. При использовании массива смотрите ограничения в {@see UserIdsList}.
     * @throws ModifyMembersError Если запрос завершился ошибкой. Исходный результат доступен через `getResult()`.
     * @throws ForbiddenException Если нет доступа к чату.
     * @throws NotFoundException Если чат не найден.
     * @link https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members
     */
    public function addMembers(int $chatId, UserIdsList|RawModel|array $userIds): void
    {
        if (is_array($userIds)) {
            $userIds = new UserIdsList($userIds);
        }

        $data = $this->httpClient->post("/chats/$chatId/members", $userIds->jsonSerialize());

        $result = ModifyMembersResult::newFromData($data);

        if (!$result->isSuccess()) {
            throw new ModifyMembersError($result);
        }
    }

    /**
     * Отправляет ответ на Callback.
     *
     * Отправляет ответ после нажатия пользователем кнопки: обновлённое сообщение и/или одноразовое уведомление.
     *
     * В отличие от обычного редактирования сообщения, при ответе на callback сервер автоматически удаляет
     * вложения, включая клавиатуру, если они не переданы в обновлённом сообщении.
     *
     * @param non-empty-string|Callback $callbackId Идентификатор кнопки, на которую нажал пользователь
     *     (pattern: '^\s*\S[\s\S]*$'). Бот получает идентификатор как часть {@see Update} с типом `message_callback`.
     *     Пример получения идентификатора: `$update->getCallback()->getCallbackId()`.
     *     Вместо идентификатора можно передать сам объект {@see Callback}.
     * @param CallbackAnswer|RawModel|NewMessageBody|non-empty-string $answer Ответ с обновлённым сообщением
     *     и/или уведомлением, объект сообщения или его текст.
     * @param bool $disableLinkPreview Если `true`, сервер не будет создавать медиапревью для ссылок в тексте
     *     обновлённого сообщения.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws NotAllowedException Если метод не разрешён.
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function answerOnCallback(
        string|Callback $callbackId,
        CallbackAnswer|RawModel|NewMessageBody|string $answer,
        bool $disableLinkPreview = false,
    ): void {
        if ($callbackId instanceof Callback) {
            $callbackId = $callbackId->getCallbackId();
        }
        if (is_string($answer)) {
            $answer = new CallbackAnswer(new NewMessageBody($answer));
        } elseif ($answer instanceof NewMessageBody) {
            $answer = new CallbackAnswer($answer);
        }

        self::validateCallbackId('callbackId', $callbackId);

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
     * Бот должен быть администратором этого чата или канала с правом {@see ChatAdminPermission::AddAdmins}.
     *
     * Alias: {@see self::deleteAdmins()}
     *
     * @param int $chatId Идентификатор группового чата или канала.
     * @param int $userId Идентификатор пользователя или бота, которого надо лишить прав администратора.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-/
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
     * Бот должен быть администратором этого чата или канала с правом {@see ChatAdminPermission::AddAdmins}.
     *
     * @param int $chatId Идентификатор группового чата или канала.
     * @param int $userId Идентификатор пользователя или бота, которого надо лишить прав администратора.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-/
     */
    public function deleteAdmins(int $chatId, int $userId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/admins/$userId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет групповой чат для всех участников.
     *
     * Удалить чат может только его владелец: у бота это чаты, созданные по кнопке создания чата
     * (тип `chat`) в его сообщении — владельцем такого чата становился сам бот. Такая кнопка удалена
     * из официальной схемы API, поэтому новые чаты боту создавать нечем. В остальных случаях, даже
     * с правами администратора, сервер отвечает ошибкой `Insufficient access rights to perform this action`.
     *
     * @param int $chatId Идентификатор чата.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @deprecated Метод удалён из официальной схемы API.
     *     На 7 ноября 2026 г. он остаётся рабочим, но может быть отключён в любой момент.
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
     * Для удаления чужих комментариев бот должен быть администратором этого канала с правом
     * {@see ChatAdminPermission::Delete}.
     *
     * Если канал архивирован или в нём отключены комментарии, старые комментарии по-прежнему можно удалять.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`),
     *     комментарий к которому нужно удалить (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-string|CommentMessage|CommentMessageBody $commentId Идентификатор удаляемого комментария
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see CommentMessage} или {@see CommentMessageBody}.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws ForbiddenException Если нет доступа к ресурсу.
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
     * Удаляет сообщение в диалоге или групповом чате либо пост в канале.
     *
     * В диалоге бот может удалять только собственные сообщения.
     * В групповом чате бот может удалять собственные сообщения без прав администратора;
     * для удаления чужих сообщений требуется право {@see ChatAdminPermission::Write}.
     * Для удаления постов в канале требуется право {@see ChatAdminPermission::Delete}.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор удаляемого сообщения или поста
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message} или {@see MessageBody}.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws ForbiddenException Если нет доступа к ресурсу.
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
     * Могут потребоваться дополнительные права.
     *
     * @param int $chatId Идентификатор чата или канала.
     * @param ChatPatch|RawModel $chatPatch Данные для изменения чата или канала.
     * @return Chat Обновлённый объект чата или канала.
     * @throws ForbiddenException Если нет доступа к чату или каналу.
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
     * Комментарии, опубликованные от имени канала, можно редактировать, только если боту назначено право
     * администратора {@see ChatAdminPermission::Edit}; без этого права бот может редактировать лишь свои комментарии.
     *
     * Комментарий можно редактировать только в течение 26 часов после публикации. Позже API возвращает HTTP 422
     * с кодом `comments.timeout_exceeded`. Это ограничение действует и для архивированного канала, и при
     * отключённых комментариях.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), комментарий к которому
     *     нужно отредактировать (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-string|CommentMessage|CommentMessageBody $commentId Идентификатор редактируемого
     *     комментария (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see CommentMessage} или {@see CommentMessageBody}.
     * @param NewCommentBody|RawModel|non-empty-string $commentBody Обновлённое содержимое комментария.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws UnprocessableEntityException Если с момента публикации комментария прошло больше 26 часов.
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
     * Редактирует сообщение в диалоге или групповом чате либо пост в канале.
     *
     * В диалоге и групповом чате бот может редактировать только собственные сообщения; права администратора для этого
     * не нужны. Для редактирования постов в канале требуется право администратора {@see ChatAdminPermission::Edit}.
     *
     * Если поле `attachments` равно `null`, текущие вложения сохраняются. Если передан пустой список,
     * все вложения удаляются.
     *
     * Ограничения:
     * - Есть особенности редактирования сообщений в диалогах с ботом, связанные со сроком давности:
     *   - сообщения с кнопками `inline_keyboard` редактируются независимо от срока давности.
     *   - остальные сообщения редактируются, если они отправлены менее 7 суток назад.
     * - В групповых чатах сообщения, а в каналах посты редактируются независимо от срока давности.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор редактируемого сообщения или поста
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Обновлённое содержимое сообщения или поста.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
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
     * Добавляет, изменяет или удаляет команды бота.
     *
     * Подсказки при вводе `/` отображаются в диалогах на iOS, Android и десктопе, а в групповых чатах — на iOS
     * и Android. На iOS в диалоге список также открывается кнопкой `/`. В каналах команды не отображаются.
     *
     * Чтобы удалить команды, передайте пустой массив `commands`.
     *
     * @param BotCommandsPatch|RawModel $commands Список команд и их описаний для отображения в подсказках
     *     (maxItems: 32).
     * @return BotCommandsInfo Обновлённая информация о командах бота.
     * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands
     */
    public function editMyCommands(BotCommandsPatch|RawModel $commands): BotCommandsInfo
    {
        $data = $this->httpClient->patch('/me/commands', $commands->jsonSerialize());

        return BotCommandsInfo::newFromData($data);
    }

    /**
     * Получает список администраторов группового чата или канала.
     *
     * Возвращает список всех администраторов группового чата или канала (пользователей и ботов), их данные,
     * а также права на управление каналом или групповым чатом для пользователей-администраторов.
     *
     * Бот должен быть администратором этого чата или канала.
     *
     * @param int $chatId Идентификатор группового чата или канала.
     * @return ChatMembersList Список администраторов в групповом чате или канале.
     * @throws ForbiddenException Если нет доступа к чату или каналу.
     * @throws NotFoundException Если ресурс не найден.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins
     */
    public function getAdmins(int $chatId): ChatMembersList
    {
        $data = $this->httpClient->get("/chats/$chatId/members/admins");

        return ChatMembersList::newFromData($data);
    }

    /**
     * Получает информацию о чате.
     *
     * @param int $chatId Идентификатор запрашиваемого чата.
     * @return Chat Информация о чате.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-
     */
    public function getChat(int $chatId): Chat
    {
        $data = $this->httpClient->get("/chats/$chatId");

        return Chat::newFromData($data);
    }

    /**
     * Получает комментарий по идентификатору.
     *
     * Возвращает информацию о комментарии к посту в канале по его идентификатору (`mid`).
     *
     * Бот должен быть администратором этого канала с правом {@see ChatAdminPermission::ReadAllMessages}.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`),
     *     к которому относится комментарий (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-string|CommentMessage|CommentMessageBody $commentId Идентификатор комментария (`mid`)
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see CommentMessage} или {@see CommentMessageBody}.
     * @return CommentMessage Информация о комментарии, идентификатор которого был передан в запросе.
     * @throws NotFoundException Если комментарий или пост не найдены либо у бота нет к ним доступа.
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
     * Комментарии в ответе расположены от старых к новым.
     *
     * Если указан `$commentIds`, возвращаются только запрошенные комментарии, а остальные параметры игнорируются.
     *
     * Бот должен быть администратором этого канала с правом {@see ChatAdminPermission::ReadAllMessages}.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относятся
     *     комментарии (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param non-empty-array<non-empty-string>|null $commentIds Список идентификаторов комментариев для получения
     *     (uniqueItems: true). Если параметр указан, остальные параметры игнорируются.
     * @param non-negative-int|null $before Запросить комментарии до указанного времени (Unix-время в миллисекундах).
     * @param non-negative-int|null $after Запросить комментарии после указанного времени (Unix-время в миллисекундах).
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

            foreach ($commentIds as $key => $commentId) {
                self::validateMid("commentIds[$key]", $commentId);
            }

            $params = ['comment_ids' => implode(',', array_unique($commentIds))];
        } else {
            self::validateInt('count', $count, 1, 100);

            $params = ['count' => $count];
            self::addTimeRangeParams($params, $before, $after);
        }

        $data = $this->httpClient->get("/messages/$messageId/comments", $params);

        return CommentMessageList::newFromData($data);
    }

    /**
     * Получает комментарии по идентификаторам.
     *
     * Возвращает запрошенные комментарии к посту в канале. Можно указать один идентификатор или несколько.
     *
     * Бот должен быть администратором этого канала с правом {@see ChatAdminPermission::ReadAllMessages}.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относятся
     *     комментарии (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message}
     *     или {@see MessageBody} — идентификатор будет извлечён из него автоматически.
     * @param non-empty-array<non-empty-string> $commentIds Список идентификаторов комментариев для получения
     *     (uniqueItems: true).
     * @return CommentMessageList Список запрошенных комментариев.
     */
    public function getCommentsById(string|Message|MessageBody $messageId, array $commentIds): CommentMessageList
    {
        return $this->getComments($messageId, $commentIds);
    }

    /**
     * Получает комментарии к посту в канале.
     *
     * Возвращает последние `$count` комментариев к указанному посту.
     * Можно указать промежуток времени, за который нужно получить комментарии.
     *
     * Комментарии в ответе расположены от старых к новым.
     *
     * Бот должен быть администратором этого канала с правом {@see ChatAdminPermission::ReadAllMessages}.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относятся
     *     комментарии (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message}
     *     или {@see MessageBody} — идентификатор будет извлечён из него автоматически.
     * @param non-negative-int|null $before Запросить комментарии до указанного времени (Unix-время в миллисекундах).
     * @param non-negative-int|null $after Запросить комментарии после указанного времени (Unix-время в миллисекундах).
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
     * @return MaxHttpClientInterface HTTP-клиент для прямых запросов к Max Messenger API.
     *     Для использования необходимо знать формат запросов и ответов API.
     */
    public function getMaxHttpClient(): MaxHttpClientInterface
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
     * @param int $chatId Идентификатор группового чата или канала.
     * @param non-empty-array<int>|null $userIds Список идентификаторов пользователей, чьё членство нужно получить
     *     (uniqueItems: true). Когда этот аргумент передан, аргументы `count` и `marker` игнорируются.
     * @param int|null $marker Указатель на следующую страницу данных.
     * @param int<1, 100> $count Максимальное количество участников в ответе.
     * @return ChatMembersList Список участников и указатель на следующую страницу данных.
     * @throws ForbiddenException Если нет доступа к чату или каналу.
     * @throws NotFoundException Если ресурс не найден.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function getMembers(
        int $chatId,
        ?array $userIds = null,
        ?int $marker = null,
        int $count = 20,
    ): ChatMembersList {
        if ($userIds !== null) {
            $userIds = array_unique(array_filter($userIds));

            self::validateArray('userIds', $userIds, minItems: 1);

            $params = ['user_ids' => implode(',', $userIds)];
        } else {
            self::validateInt('count', $count, minimum: 1, maximum: 100);

            $params = ['count' => $count];
            if ($marker !== null) {
                $params['marker'] = $marker;
            }
        }

        $data = $this->httpClient->get("/chats/$chatId/members", $params);

        return ChatMembersList::newFromData($data);
    }

    /**
     * Получает информацию о членстве бота в групповом чате или канале.
     *
     * Возвращает информацию о членстве бота в групповом чате или канале, общую информацию о нём,
     * а также список прав доступа.
     *
     * @param int $chatId Идентификатор группового чата или канала.
     * @return ChatMember Информация о членстве бота.
     * @throws ForbiddenException Если нет доступа к чату или каналу.
     * @throws NotFoundException Если ресурс не найден.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/me
     */
    public function getMembership(int $chatId): ChatMember
    {
        $data = $this->httpClient->get("/chats/$chatId/members/me");

        return ChatMember::newFromData($data);
    }

    /**
     * Получает сообщение или пост.
     *
     * Возвращает одно сообщение из чата или пост из канала по его идентификатору.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор сообщения или поста (`mid`)
     *     (pattern: '^mid\.[a-zA-Z0-9_\-]+$'). Также можно передать объект {@see Message} или {@see MessageBody}.
     * @return Message Запрошенное сообщение или пост.
     * @throws NotFoundException Если сообщение не найдено или у бота нет к нему доступа.
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
     * Возвращает сообщения из чата или посты из канала. Можно ограничить выборку промежутком времени и количеством.
     *
     * Для выполнения запроса нужно указать `$chatId` или `$messageIds`:
     *
     * - `$chatId` — идентификатор чата или канала. Сообщения или посты возвращаются в обратном порядке:
     *   последние будут первыми в списке.
     * - `$messageIds` — список идентификаторов сообщений или постов (`mid`). Можно указать один или несколько.
     *
     * @param non-empty-array<non-empty-string>|null $messageIds Список идентификаторов сообщений или постов
     *     (uniqueItems: true). Обязательный параметр, если не указан `$chatId`.
     * @param int|null $chatId Идентификатор чата или канала. Обязательный параметр, если не указан `$messageIds`.
     * @param non-negative-int|null $before Запросить сообщения или посты до указанного времени
     *     (Unix-время в миллисекундах).
     * @param non-negative-int|null $after Запросить сообщения или посты после указанного времени
     *     (Unix-время в миллисекундах).
     * @param int<1, 100> $count Максимальное количество сообщений или постов в ответе.
     * @return MessageList Список сообщений или постов.
     * @throws ForbiddenException Если пользователь приостановил бота или у бота нет доступа к чату.
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function getMessages(
        ?array $messageIds = null,
        ?int $chatId = null,
        ?int $before = null,
        ?int $after = null,
        int $count = 50,
    ): MessageList {
        if ($messageIds !== null) {
            self::validateArray('messageIds', $messageIds, minItems: 1);
            foreach ($messageIds as $key => $messageId) {
                self::validateMid("messageIds[$key]", $messageId);
            }

            $params = ['message_ids' => implode(',', array_unique($messageIds))];
        } else {
            self::validateNotNull('chatId', $chatId);
            self::validateInt('count', $count, minimum: 1, maximum: 100);

            $params = ['chat_id' => $chatId, 'count' => $count];
            self::addTimeRangeParams($params, $before, $after);
        }

        $data = $this->httpClient->get('/messages', $params);

        return MessageList::newFromData($data);
    }

    /**
     * Получает сообщения или посты по идентификаторам.
     *
     * Возвращает запрошенные сообщения или посты. Можно указать один идентификатор или несколько.
     *
     * @param non-empty-array<non-empty-string> $messageIds Список идентификаторов сообщений или постов для получения
     *     (uniqueItems: true).
     * @return MessageList Список запрошенных сообщений или постов.
     * @throws ForbiddenException Если пользователь приостановил бота или у бота нет доступа к чату.
     */
    public function getMessagesById(array $messageIds): MessageList
    {
        return $this->getMessages($messageIds);
    }

    /**
     * Получает сообщения из чата или посты из канала.
     *
     * Возвращает сообщения из указанного чата или посты из указанного канала. Можно ограничить выборку промежутком
     * времени и количеством. Сообщения и посты возвращаются в обратном порядке: последние будут первыми в списке.
     *
     * @param int $chatId Идентификатор чата или канала.
     * @param non-negative-int|null $before Запросить сообщения или посты до указанного времени
     *     (Unix-время в миллисекундах).
     * @param non-negative-int|null $after Запросить сообщения или посты после указанного времени
     *     (Unix-время в миллисекундах).
     * @param int<1, 100> $count Максимальное количество сообщений или постов в ответе.
     * @return MessageList Список сообщений или постов.
     * @throws ForbiddenException Если пользователь приостановил бота или у бота нет доступа к чату.
     */
    public function getMessagesFromChat(
        int $chatId,
        ?int $before = null,
        ?int $after = null,
        int $count = 50,
    ): MessageList {
        return $this->getMessages(null, $chatId, $before, $after, $count);
    }

    /**
     * Получает информацию о боте.
     *
     * Возвращает информацию о данном боте, в том числе идентификатор, имя и аватар при его наличии.
     * Бот определяется по токену доступа.
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
     * Бот должен быть администратором этого чата или канала с правом {@see ChatAdminPermission::ReadAllMessages}.
     *
     * @param int $chatId Идентификатор чата или канала.
     * @return GetPinnedMessageResult Результат с закреплённым сообщением или постом.
     * @throws ForbiddenException Если нет доступа к чату или каналу.
     * @throws NotFoundException Если ресурс не найден.
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/pin
     */
    public function getPinnedMessage(int $chatId): GetPinnedMessageResult
    {
        $data = $this->httpClient->get("/chats/$chatId/pin");

        return GetPinnedMessageResult::newFromData($data);
    }

    /**
     * Получает Webhook-подписки.
     *
     * Возвращает список всех Webhook-подписок данного бота.
     *
     * @return GetSubscriptionsResult Результат со списком подписок.
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
     * При передаче `$marker` все предыдущие события считаются подтверждёнными.
     * Если `$marker` не передан, бот получит все события, произошедшие после последнего подтверждения.
     *
     * Метод доступен, если бот не подписан на доставку событий через Webhook. Long Polling ограничен по скорости
     * и времени хранения событий и не подходит для production-окружения. На всех этапах рекомендуется Webhook.
     *
     * @param int<1, 1000> $limit Максимальное количество событий для получения.
     * @param int<0, 90> $timeout Тайм-аут в секундах для долгого опроса.
     * @param int|null $marker Маркер для получения событий с конкретной позиции.
     *     Для получения событий после последнего подтверждения передайте `null`.
     * @param array<UpdateType|string>|null $types Список типов событий, которые вы хотите получать
     *     (uniqueItems: true). Полный список возможных событий смотрите в описании класса {@see Update}.
     * @return UpdateList Список событий и маркер следующего ожидаемого события.
     * @throws NotAllowedException Если метод не разрешён.
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function getUpdates(
        int $limit = 100,
        int $timeout = 30,
        ?int $marker = null,
        ?array $types = null,
    ): UpdateList {
        self::validateInt('limit', $limit, 1, 1000);
        self::validateInt('timeout', $timeout, 0, 90);

        $params = [
            'limit' => $limit,
            'timeout' => $timeout,
        ];
        if ($marker !== null) {
            $params['marker'] = $marker;
        }
        if ($types !== null) {
            $params['types'] = implode(',', array_unique(array_map(
                static fn(UpdateType|string $type): string => $type instanceof UpdateType ? $type->value : $type,
                $types,
            )));
        }

        $data = $this->httpClient->getUpdates('/updates', $params, ($timeout + 10) * 1000);

        return UpdateList::newFromData($data);
    }

    /**
     * Получает URL для загрузки медиафайлов.
     *
     * Возвращает URL для последующей загрузки медиафайла.
     *
     * Для загрузки медиафайлов на сервера рекомендуется использовать
     * {@link https://github.com/max-messenger-bot/max-bot-uploader-php max-bot-uploader-php}.
     *
     * Типы медиафайлов, поддерживаемые форматы и ограничения размера описаны в {@see UploadType}.
     *
     * По URL-ссылке, которая вернётся в ответ на запрос, можно загрузить только один файл. Если вы хотите загрузить
     * ещё файл, отправьте запрос повторно и используйте новую URL-ссылку.
     *
     * @param UploadType $type Тип загружаемого медиафайла.
     * @return UploadEndpoint URL для загрузки медиафайла и, при наличии, токен для отправки вложения.
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function getUploadUrl(UploadType $type): UploadEndpoint
    {
        $data = $this->httpClient->post('/uploads', null, ['type' => $type->value]);

        return UploadEndpoint::newFromData($data);
    }

    /**
     * Получает информацию о видео, прикреплённом к сообщению или посту.
     *
     * Возвращает подробную информацию о видео, прикреплённом к сообщению в чате или посту в канале:
     * URL-адреса воспроизведения и дополнительные метаданные.
     *
     * @param non-empty-string $videoToken Токен видеовложения (pattern: '^vid\.[\x21-\x7E]+$').
     * @return VideoAttachmentDetails Подробная информация о видео.
     * @throws ForbiddenException Если нет доступа к ресурсу.
     * @throws NotFoundException Если сообщение не найдено или у бота нет к нему доступа.
     * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-
     */
    public function getVideoAttachmentDetails(string $videoToken): VideoAttachmentDetails
    {
        self::validateVideoToken('videoToken', $videoToken);

        $videoToken = rawurlencode($videoToken);
        $data = $this->httpClient->get("/videos/$videoToken");

        return VideoAttachmentDetails::newFromData($data);
    }

    /**
     * Удаляет данного бота из группового чата или канала.
     *
     * @param int $chatId Идентификатор группового чата или канала.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws ForbiddenException Если нет доступа к чату.
     * @throws NotFoundException Если ресурс не найден.
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
     * Бот должен быть администратором этого чата или канала с правом {@see ChatAdminPermission::PinMessage}.
     *
     * @param int $chatId Идентификатор группового чата или канала, где нужно закрепить сообщение или пост.
     * @param PinMessageBody|RawModel|non-empty-string $pinMessage Данные для закрепления или идентификатор
     *     сообщения или поста.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws ForbiddenException Если нет доступа к ресурсу.
     * @throws NotFoundException Если ресурс не найден.
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
     * Бот должен быть администратором этого чата или канала с правом {@see ChatAdminPermission::AddAdmins}.
     * Метод нельзя применять к владельцу чата или канала и к самому боту.
     *
     * Права, которые можно назначить, зависят от того, где (канал или чат) и кому (пользователь или бот) выданы.
     * Если вы хотите изменить назначенные права, вызовите повторно текущий метод с обновлённым списком прав.
     * Полный список прав администратора и условия их назначения описаны в {@see ChatAdminPermission}.
     *
     * @param int $chatId Идентификатор группового чата или канала.
     * @param ChatAdminsList|RawModel|ChatAdmin[] $admins Список назначаемых администраторов.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
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
     * Бот должен быть администратором этого чата или канала с правом {@see ChatAdminPermission::AddRemoveMembers}.
     *
     * @param int $chatId Идентификатор группового чата или канала.
     * @param int $userId Идентификатор пользователя, которого нужно удалить из группового чата или канала.
     * @param bool $block Если передать `true`, пользователь будет заблокирован в чате.
     *     Применяется только для чатов с публичной или приватной ссылкой. Игнорируется в остальных случаях.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws ForbiddenException Если нет доступа к ресурсу.
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
     * Отправляет действие бота.
     *
     * Отправляет в диалог или групповой чат действие бота, например «набор текста» или «отправка фото».
     *
     * Для каналов сервер принимает запрос, но участникам действие не показывается.
     *
     * @param int $chatId Идентификатор диалога или группового чата.
     * @param ActionRequestBody|RawModel|SenderAction $action Действие бота для отправки участникам чата.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
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
     * Возвращает информацию о созданном комментарии.
     *
     * Для отправки комментария в настройках канала должны быть включены комментарии, а бот должен быть администратором
     * этого канала с правом {@see ChatAdminPermission::Write}.
     *
     * @param non-empty-string|Message|MessageBody $messageId Идентификатор поста (`mid`), к которому относится
     *     комментарий (pattern: '^mid\.[a-zA-Z0-9_\-]+$').
     *     Также можно передать объект {@see Message} или {@see MessageBody}.
     * @param NewCommentBody|RawModel|non-empty-string $commentBody Тело нового комментария.
     * @param bool $disableLinkPreview Если `true`, сервер не будет создавать медиапревью для ссылок в тексте.
     * @return SendCommentResult Информация о созданном комментарии.
     * @link https://dev.max.ru/docs-api/methods/POST/messages/-messageId-/comments
     */
    public function sendComment(
        string|Message|MessageBody $messageId,
        NewCommentBody|RawModel|string $commentBody,
        bool $disableLinkPreview = false,
    ): SendCommentResult {
        $messageId = self::extractMid($messageId, 'messageId');

        if (is_string($commentBody)) {
            $commentBody = new NewCommentBody($commentBody);
        }

        $params = $disableLinkPreview ? ['disable_link_preview' => 'true'] : null;
        $data = $this->httpClient->post("/messages/$messageId/comments", $commentBody->jsonSerialize(), $params);

        return SendCommentResult::newFromData($data);
    }

    /**
     * Отправляет сообщение или пост.
     *
     * Отправляет сообщение в диалог или групповой чат либо пост в канал. Возвращает информацию о созданном сообщении
     * или посте. Необходимо указать только один из аргументов `$userId` и `$chatId`.
     *
     * Для отправки поста в канал бот должен быть администратором с правом {@see ChatAdminPermission::Write}.
     *
     * При отправке пользователю бот может получить ошибку `chat.denied`, если пользователь остановил или удалил бота,
     * `dialog.not.found`, если пользователь не запускал бота, или `chat.not.found`, если пользователь не существует
     * или заблокирован. Код ошибки доступен через `$exception->getError()->getCode()`.
     *
     * Для прикрепления медиафайла сначала получите URL загрузки с помощью {@see self::getUploadUrl()} и загрузите файл.
     *
     * @param int|null $userId Идентификатор пользователя, которому отправляется сообщение.
     * @param int|null $chatId Идентификатор чата или канала, куда отправляется сообщение или пост.
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Содержимое нового сообщения или поста.
     * @param bool $disableLinkPreview Если `true`, сервер не будет создавать медиапревью для ссылок в тексте.
     *     Параметр действует для этого сообщения или поста, в том числе при дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении или посте.
     * @throws ForbiddenException Если отправка или пересылка запрещена, в том числе при ошибке `chat.denied`.
     * @throws NotFoundException Если получатель или диалог не найден, в том числе при ошибках `dialog.not.found`
     *     и `chat.not.found`.
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
            self::validateNotNull('userId', $userId);

            $params['user_id'] = $userId;
        }
        if ($disableLinkPreview) {
            $params['disable_link_preview'] = 'true';
        }

        if (is_string($messageBody)) {
            $messageBody = new NewMessageBody($messageBody);
        }

        $retryAttempts = $this->config->getAttachmentRetryAttempts();
        do {
            try {
                $data = $this->httpClient->post('/messages', $messageBody->jsonSerialize(), $params);

                return SendMessageResult::newFromData($data);
            } catch (BadRequestException $e) {
                if (!$retryAttempts || $e->getError()?->isAttachmentNotReady() !== true) {
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
     * Отправляет сообщение в диалог или групповой чат либо пост в канал.
     *
     * Возвращает информацию о созданном сообщении или посте.
     *
     * Для отправки поста в канал бот должен быть администратором с правом {@see ChatAdminPermission::Write}.
     *
     * @param int $chatId Идентификатор диалога, группового чата или канала.
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Содержимое нового сообщения или поста.
     * @param bool $disableLinkPreview Если `true`, сервер не будет создавать медиапревью для ссылок в тексте.
     *     Параметр действует для этого сообщения или поста, в том числе при дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении или посте.
     * @throws ForbiddenException Если отправка запрещена.
     * @throws NotFoundException Если чат или канал не найден.
     */
    public function sendMessageToChat(
        int $chatId,
        NewMessageBody|RawModel|string $messageBody,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->sendMessage(null, $chatId, $messageBody, $disableLinkPreview);
    }

    /**
     * Отправляет сообщение пользователю.
     *
     * Отправляет сообщение в диалог с пользователем и возвращает информацию о созданном сообщении.
     * Бот может отправить сообщение пользователю только после того, как пользователь запустит бота в личном диалоге.
     *
     * При отправке пользователю бот может получить ошибку `chat.denied`, если пользователь остановил или удалил бота,
     * `dialog.not.found`, если пользователь не запускал бота, или `chat.not.found`, если пользователь не существует
     * или заблокирован. Код ошибки доступен через `$exception->getError()->getCode()`.
     *
     * @param int $userId Идентификатор пользователя, которому отправляется сообщение.
     * @param NewMessageBody|RawModel|non-empty-string $messageBody Содержимое нового сообщения.
     * @param bool $disableLinkPreview Если `true`, сервер не будет создавать медиапревью для ссылок в тексте.
     *     Параметр действует для этого сообщения, в том числе при его дальнейшем редактировании.
     * @return SendMessageResult Информация о созданном сообщении.
     * @throws ForbiddenException Если отправка запрещена, в том числе при ошибке `chat.denied`.
     * @throws NotFoundException Если пользователь или диалог не найден, в том числе при ошибках `dialog.not.found`
     *     и `chat.not.found`.
     */
    public function sendMessageToUser(
        int $userId,
        NewMessageBody|RawModel|string $messageBody,
        bool $disableLinkPreview = false,
    ): SendMessageResult {
        return $this->sendMessage($userId, null, $messageBody, $disableLinkPreview);
    }

    /**
     * @param Closure(non-empty-string $method, TemporaryExceptionInterface|BadRequestException $exception): void|null $exceptionLogger
     *     Обработчик, вызываемый при ошибке перед повторной попыткой запроса.
     *     Этот же обработчик передаётся HTTP-клиенту. `null` отключает логирование.
     * @return $this
     */
    public function setExceptionLogger(?Closure $exceptionLogger): static
    {
        $this->exceptionLogger = $exceptionLogger;
        $this->httpClient->setExceptionLogger($exceptionLogger);

        return $this;
    }

    /**
     * Подписывает данного бота на доставку событий через Webhook.
     *
     * Настраивает доставку событий бота через Webhook — основной механизм получения событий в продуктовых интеграциях.
     * При активной подписке Long Polling не работает.
     *
     * После вызова этого метода события отправляются на указанный Webhook-endpoint в виде HTTPS POST-запросов
     * с объектом {@see Update}. Сервер должен принимать соединения на порту 443.
     *
     * > При доставке событий МАКС не принимает самоподписанные сертификаты Webhook-сервера.
     * >
     * > Webhook-endpoint должен ответить HTTP 200 в течение 30 секунд, иначе доставка считается неуспешной.
     *
     * @param SubscriptionRequestBody|RawModel $subscription Параметры Webhook-подписки.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
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
     * Бот должен быть администратором этого чата или канала с правом {@see ChatAdminPermission::PinMessage}.
     *
     * @param int $chatId Идентификатор группового чата или канала, в котором нужно открепить сообщение или пост.
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @throws ForbiddenException Если нет доступа к ресурсу.
     * @throws NotFoundException Если ресурс не найден.
     * @link https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/pin
     */
    public function unpinMessage(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/pin");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Удаляет Webhook-подписку данного бота.
     *
     * Отписывает бота от получения новых событий через указанный Webhook.
     *
     * После вызова этого метода бот перестаёт получать новые события через Webhook и становится доступна
     * доставка уведомлений через Long Polling (метод с длительным опросом).
     * Long Polling ограничен по скорости и времени хранения событий и не подходит для production-окружения.
     * На всех этапах рекомендуется использовать Webhook.
     *
     * @param non-empty-string $url URL удаляемой Webhook-подписки (minLength: 12).
     * @throws SimpleQueryError Если запрос завершился ошибкой.
     * @link https://dev.max.ru/docs-api/methods/DELETE/subscriptions
     */
    public function unsubscribe(string $url): void
    {
        self::validateSubscriptionUrl('url', $url);

        $data = $this->httpClient->delete('/subscriptions', ['url' => $url]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Проверяет соответствие хеша и значения vcfInfo.
     *
     * Позволяет проверить, что пользователь поделился номером телефона, привязанным к его профилю в МАКС.
     *
     * Если проверка прошла успешно, вы можете получить номер телефона следующим способом:
     * ```
     * $phones = $payload->getPhones();
     * ```
     *
     * @param ContactAttachmentPayload $payload Данные контакта с хешем и VCF-информацией для проверки.
     * @return bool `true`, если подпись совпадает; `false`, если она не совпадает или данных для проверки недостаточно.
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

        $decodedVcf = str_replace(['\\r\\n', '\\n', '\\r'], ["\r\n", "\n", "\r"], $vcfInfo);
        $normalizedVcf = str_replace(["\r\n", "\r"], "\n", $decodedVcf);

        foreach (array_unique([$vcfInfo, $decodedVcf, $normalizedVcf]) as $candidate) {
            $signature = hash_hmac('sha256', $candidate, $accessToken, true);
            $base64 = base64_encode($signature);
            $base64Url = strtr($base64, '+/', '-_');

            if (hash_equals(bin2hex($signature), strtolower($hash))) {
                return true;
            }

            foreach ([$base64, rtrim($base64, '='), $base64Url, rtrim($base64Url, '=')] as $encoded) {
                if (hash_equals($encoded, $hash)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array<string, int> $params
     */
    private static function addTimeRangeParams(array &$params, ?int $before, ?int $after): void
    {
        if ($before !== null) {
            self::validateInt('before', $before, 0);

            $params['before'] = $before;
        }
        if ($after !== null) {
            self::validateInt('after', $after, 0);

            $params['after'] = $after;
        }
        if ($before !== null && $after !== null) {
            self::validateInt('after', $after, maximum: $before);
        }
    }

    private function checkSimpleQueryResult(array $data): void
    {
        $result = SimpleQueryResult::newFromData($data);

        if (!$result->isSuccess()) {
            throw new SimpleQueryError($result);
        }
    }

    /**
     * @param non-empty-string|Message|MessageBody|CommentMessage|CommentMessageBody $id
     * @param non-empty-string $argumentName
     * @return non-empty-string
     * @psalm-taint-escape file
     * @psalm-taint-escape ssrf
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

        self::validateMid($argumentName, $id);

        return $id;
    }
}
