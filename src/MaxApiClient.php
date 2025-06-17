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
     * Add members.
     *
     * Adds members to chat. Additional permissions may require.
     *
     * @param int $chatId Chat identifier.
     * @param int[]|UserIdsList|RawModel $userIds
     * @api
     */
    public function addMembers(int $chatId, array|UserIdsList|RawModel $userIds): void
    {
        if (is_array($userIds)) {
            $userIds = new UserIdsList($userIds);
        }

        $data = $this->httpClient->post("/chats/$chatId/members", $userIds->getRawData());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Answer on callback.
     *
     * This method should be called to send an answer after a user has clicked the button.
     * The answer may be an updated message or/and a one-time user notification.
     *
     * @param non-empty-string $callbackId Identifies a button clicked by user (minLength: 1, pattern: ^(?!\s*$).+).
     *     Bot receives this identifier after user pressed button as part of `MessageCallbackUpdate`.
     * @param CallbackAnswer|RawModel $answer Answer data.
     * @api
     */
    public function answerOnCallback(string $callbackId, CallbackAnswer|RawModel $answer): void
    {
        static::validateString('callbackId', $callbackId, minLength: 1);

        $data = $this->httpClient->post('/answers', $answer->getRawData(), ['callback_id' => $callbackId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Revoke admin rights.
     *
     * Revokes admin rights from a user in the chat by removing their administrative privileges.
     *
     * @param int $chatId Chat identifier.
     * @param int $userId User identifier.
     * @api
     */
    public function deleteAdmins(int $chatId, int $userId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/admins/$userId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Delete chat.
     *
     * Deletes chat for all participants.
     *
     * @param int $chatId Chat identifier.
     * @api
     */
    public function deleteChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Delete message.
     *
     * Deletes message in a dialog or in a chat if bot has permission to delete messages.
     *
     * @param non-empty-string $messageId Deleting message identifier.
     * @api
     */
    public function deleteMessage(string $messageId): void
    {
        $data = $this->httpClient->delete('/messages', ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Edit chat info.
     *
     * @param int $chatId Chat identifier.
     * @param ChatPatch|RawModel $chatPatch Chat patch data.
     * @return Chat Updated chat object.
     * @api
     */
    public function editChat(int $chatId, ChatPatch|RawModel $chatPatch): Chat
    {
        $data = $this->httpClient->patch("/chats/$chatId", $chatPatch->getRawData());

        return Chat::newFromData($data);
    }

    /**
     * Edit message.
     *
     * Updated message should be sent as `NewMessageBody` in a request body.
     * In case `attachments` field is `null`, the current message attachments won’t be changed.
     * In case of sending an empty list in this field, all attachments will be deleted.
     *
     * @param non-empty-string $messageId Editing message identifier (minLength: 1).
     * @param NewMessageBody|RawModel $message New message body.
     * @api
     */
    public function editMessage(string $messageId, NewMessageBody|RawModel $message): void
    {
        static::validateString('messageId', $messageId, minLength: 1);

        $data = $this->httpClient->put('/messages', $message->getRawData(), ['message_id' => $messageId]);

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Edit current bot info.
     *
     * Edits current bot info.
     *
     * @return BotInfo Modified bot info.
     * @api
     */
    public function editMyInfo(BotPatch|RawModel $botPatch): BotInfo
    {
        $data = $this->httpClient->patch('/me', $botPatch->getRawData());

        return BotInfo::newFromData($data);
    }

    /**
     * Get chat admins.
     *
     * Returns all chat administrators. Bot must be **administrator** in requested chat.
     *
     * @param int $chatId Chat identifier.
     * @return ChatMembersList Administrators list.
     * @api
     */
    public function getAdmins(int $chatId): ChatMembersList
    {
        $data = $this->httpClient->get("/chats/$chatId/members/admins");

        return ChatMembersList::newFromData($data);
    }

    /**
     * Get chat.
     *
     * Returns info about chat.
     *
     * @param int $chatId Requested chat identifier.
     * @return Chat Chat information.
     * @api
     */
    public function getChat(int $chatId): Chat
    {
        $data = $this->httpClient->get("/chats/$chatId");

        return Chat::newFromData($data);
    }

    /**
     * Get chat by link.
     *
     * Returns chat/channel information by its public link or dialog with user by username.
     *
     * @param non-empty-string $chatLink Public chat link or username (pattern: '@?[A-Za-z]+[A-Za-z0-9-_]*').
     * @return Chat Chat information.
     * @api
     */
    public function getChatByLink(string $chatLink): Chat
    {
        if (!preg_match('/^@?[A-Za-z]+[A-Za-z0-9-_]*$/', $chatLink)) {
            throw new MatchException('chatLink');
        }

        $data = $this->httpClient->get("/chats/$chatLink");

        return Chat::newFromData($data);
    }

    /**
     * Get all chats.
     *
     * Returns information about chats that bot participated in: a result list and marker points to the next page
     *
     * @param positive-int $count Number of chats requested (minimum: 1, maximum: 100).
     * @param int|null $marker Points to next data page. `null` for the first page.
     * @return ChatList Returns paginated response of chats.
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
     * HTTP client for raw requests to Max Messenger API.
     *
     * To use it, you need to know the format of API requests and responses.
     *
     * @api
     */
    public function getHttpClient(): MaxHttpClient
    {
        return $this->httpClient;
    }

    /**
     * Get members.
     *
     * Returns users participated in chat.
     *
     * @param int $chatId Chat identifier.
     * @param int[]|null $userIds List of users identifiers to get their membership.
     *     When this parameter is passed, both `count` and `marker` are ignored.
     * @param int|null $marker Marker.
     * @param int<1, 100> $count Maximum amount of members in response (minimum: 1, maximum: 100).
     * @return ChatMembersList Returns members list and pointer to the next data page.
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
     * Get chat membership.
     *
     * Returns chat membership info for current bot.
     *
     * @param int $chatId Chat identifier.
     * @return ChatMember Current bot membership info.
     * @api
     */
    public function getMembership(int $chatId): ChatMember
    {
        $data = $this->httpClient->get("/chats/$chatId/members/me");

        return ChatMember::newFromData($data);
    }

    /**
     * Get message.
     *
     * Returns single message by its identifier.
     *
     * @param non-empty-string $messageId Message identifier (`mid`) to get single message in chat.
     * @return Message Returns single message.
     * @api
     */
    public function getMessageById(string $messageId): Message
    {
        $data = $this->httpClient->get("/messages/$messageId");

        return Message::newFromData($data);
    }

    /**
     * Get messages.
     *
     * Returns messages in chat: result page and marker referencing to the next page.
     * Messages traversed in reverse direction so the latest message in chat will be first in result array.
     * Therefore if you use `from` and `to` parameters, `to` must be **less than** `from`.
     *
     * - Get messages from chat {@see getMessagesFromChat()}
     * - Get messages by id {@see getMessagesById()}
     *
     * @param array<non-empty-string>|null $messageIds List of message ids (`mid`) to get.
     * @param int|null $chatId Chat identifier to get messages in chat.
     * @param int|null $from Start time for requested messages (Unix timestamp).
     * @param int|null $to End time for requested messages (Unix timestamp).
     * @param int<1, 100> $count Maximum amount of messages in response (minimum: 1, maximum: 100).
     * @return MessageList Returns list of messages.
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
     * Get messages by id.
     *
     * @param array<non-empty-string> $messageIds List of message ids (`mid`) to get.
     * @return MessageList Returns list of messages.
     * @api
     */
    public function getMessagesById(array $messageIds): MessageList
    {
        return $this->getMessages($messageIds);
    }

    /**
     * Get messages from chat.
     *
     * Returns messages in chat: result page and marker referencing to the next page.
     * Messages traversed in reverse direction so the latest message in chat will be first in result array.
     * Therefore if you use `from` and `to` parameters, `to` must be **less than** `from`.
     *
     * @param int $chatId Chat identifier to get messages in chat.
     * @param int|null $from Start time for requested messages (Unix timestamp).
     * @param int|null $to End time for requested messages (Unix timestamp).
     * @param int<1, 100> $count Maximum amount of messages in response (minimum: 1, maximum: 100).
     * @return MessageList Returns list of messages.
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
     * Get current bot info.
     *
     * Returns info about current bot.
     * Current bot can be identified by access token. Method returns bot identifier, name and avatar (if any).
     *
     * @return BotInfo Bot info.
     * @api
     */
    public function getMyInfo(): BotInfo
    {
        $data = $this->httpClient->get('/me');

        return BotInfo::newFromData($data);
    }

    /**
     * Get pinned message.
     *
     * Get pinned message in chat or channel.
     *
     * @param int $chatId Chat identifier to get its pinned message.
     * @return GetPinnedMessageResult Pinned message.
     * @api
     */
    public function getPinnedMessage(int $chatId): GetPinnedMessageResult
    {
        $data = $this->httpClient->get("/chats/$chatId/pin");

        return GetPinnedMessageResult::newFromData($data);
    }

    /**
     * Get subscriptions.
     *
     * In case your bot gets data via WebHook, the method returns list of all subscriptions.
     *
     * @api
     */
    public function getSubscriptions(): GetSubscriptionsResult
    {
        $data = $this->httpClient->get('/subscriptions');

        return GetSubscriptionsResult::newFromData($data);
    }

    /**
     * Get updates.
     *
     * You can use this method for getting updates in case your bot is not subscribed to WebHook.
     * The method is based on long polling.
     *
     * Every update has its own sequence number. `marker` property in response points to the next upcoming update.
     *
     * All previous updates are considered as *committed* after passing `marker` parameter.
     * If `marker` parameter is **not passed**, your bot will get all updates happened after the last commitment.
     *
     * @param int<1, 1000> $limit Maximum number of updates to be retrieved (minimum: 1, maximum: 1000).
     * @param int<0, 90> $timeout Timeout in seconds for long polling (minimum: 0, maximum: 90).
     * @param int|null $marker Pass `null` to get updates you didn't get yet.
     * @param array<UpdateType|string>|null $types List of update types your bot want to receive.
     * @return UpdateList List of updates.
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
     * Get upload URL.
     *
     * Returns the URL for the subsequent file upload.
     *
     * Two types of an upload are supported:
     * - single request upload (multipart request)
     * - and resumable upload.
     *
     * @param UploadType $type Uploaded file type.
     * @return UploadEndpoint Returns URL to upload attachment.
     * @api
     */
    public function getUploadUrl(UploadType $type): UploadEndpoint
    {
        $data = $this->httpClient->post('/uploads', ['type' => $type->value]);

        return UploadEndpoint::newFromData($data);
    }

    /**
     * Get video details.
     *
     * Returns detailed information about video attachment: playback URLs and additional metadata.
     *
     * @param non-empty-string $videoToken Video attachment token (pattern: '[A-Za-z0-9_\\-]+').
     * @return VideoAttachmentDetails Detailed video attachment info.
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
     * Leave chat.
     *
     * Removes bot from chat members.
     *
     * @param int $chatId Chat identifier.
     * @api
     */
    public function leaveChat(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/members/me");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Pin message.
     *
     * Pins message in chat or channel.
     *
     * @param int $chatId Chat identifier where message should be pinned.
     * @param non-empty-string|PinMessageBody|RawModel $pinMessage
     * @api
     */
    public function pinMessage(int $chatId, string|PinMessageBody|RawModel $pinMessage): void
    {
        if (is_string($pinMessage)) {
            $pinMessage = new PinMessageBody($pinMessage);
        }

        $data = $this->httpClient->put("/chats/$chatId/pin", $pinMessage->getRawData());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Set chat admins.
     *
     * Returns true if all administrators added.
     *
     * @param int $chatId Chat identifier.
     * @param ChatAdmin[]|ChatAdminsList|RawModel $admins
     * @api
     */
    public function postAdmins(int $chatId, array|ChatAdminsList|RawModel $admins): void
    {
        if (is_array($admins)) {
            $admins = new ChatAdminsList($admins);
        }

        $data = $this->httpClient->post("/chats/$chatId/members/admins", $admins->getRawData());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Remove member.
     *
     * Removes member from chat. Additional permissions may require.
     *
     * @param int $chatId Chat identifier.
     * @param int $userId User id to remove from chat.
     * @param bool $block Set to `true` if user should be blocked in chat.
     *     Applicable only for chats that have public or private link. Ignored otherwise.
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
     * Send action.
     *
     * Send bot action to chat.
     *
     * @param int $chatId Chat identifier.
     * @api
     */
    public function sendAction(int $chatId, SenderAction|ActionRequestBody|RawModel $action): void
    {
        if ($action instanceof SenderAction) {
            $action = new ActionRequestBody($action);
        }

        $data = $this->httpClient->post("/chats/$chatId/actions", $action->getRawData());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Send message.
     *
     * Sends a message to a chat.
     * As a result for this method new message identifier returns.
     *
     * @param int|null $userId Fill this parameter if you want to send message to user.
     * @param int|null $chatId Fill this if you send message to chat.
     * @param NewMessageBody|RawModel|null $message New message body.
     * @psalm-param NewMessageBody|RawModel $message
     * @param bool $disableLinkPreview If `false`, server will not generate media preview for links in text.
     * @return SendMessageResult Returns info about created message.
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

        $data = $this->httpClient->post('/messages', $message->getRawData(), $params);

        return SendMessageResult::newFromData($data);
    }

    /**
     * Send message to Chat.
     *
     * Sends a message to a chat.
     * As a result for this method new message identifier returns.
     *
     * @param int $chatId Fill this if you send message to chat.
     * @param NewMessageBody|RawModel $message New message body.
     * @param bool $disableLinkPreview If `false`, server will not generate media preview for links in text.
     * @return SendMessageResult Returns info about created message.
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
     * Send message to User.
     *
     * Sends a message to a user.
     * As a result for this method new message identifier returns.
     *
     * @param int $userId Fill this parameter if you want to send message to user.
     * @param NewMessageBody|RawModel $message New message body.
     * @param bool $disableLinkPreview If `false`, server will not generate media preview for links in text.
     * @return SendMessageResult Returns info about created message.
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
     * Subscribe.
     *
     * Subscribes bot to receive updates via WebHook. After calling this method,
     * the bot will receive notifications about new events in chat rooms at the specified URL.
     *
     * Your server **must** be listening on one of the following ports: **80, 8080, 443, 8443, 16384-32383**.
     *
     * @api
     */
    public function subscribe(SubscriptionRequestBody|RawModel $subscription): void
    {
        $data = $this->httpClient->post('/subscriptions', $subscription->getRawData());

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Unpin message.
     *
     * Unpins message in chat or channel.
     *
     * @param int $chatId Chat identifier to remove pinned message.
     * @api
     */
    public function unpinMessage(int $chatId): void
    {
        $data = $this->httpClient->delete("/chats/$chatId/pin");

        $this->checkSimpleQueryResult($data);
    }

    /**
     * Unsubscribe.
     *
     * Unsubscribes bot from receiving updates via WebHook. After calling the method,
     * the bot stops receiving notifications about new events.
     * Notification via the long-poll API becomes available for the bot.
     *
     * @param non-empty-string $url URL to remove from WebHook subscriptions.
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
