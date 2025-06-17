<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\HttpClient\MaxHttpClient;
use MaxMessenger\Bot\Models\Requests\BotPatch;
use MaxMessenger\Bot\Models\Requests\RawData;
use MaxMessenger\Bot\Models\Requests\UserIdsList;
use MaxMessenger\Bot\Models\Response\BotInfo;
use MaxMessenger\Bot\Models\Response\Chat;
use MaxMessenger\Bot\Models\Response\GetSubscriptionsResult;
use MaxMessenger\Bot\Models\Response\SimpleQueryResult;

use function is_string;

/**
 * @api
 */
final readonly class MaxApiClient
{
    private MaxHttpClient $httpClient;

    public function __construct(string|MaxApiConfigInterface $accessTokenOrConfig)
    {
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
     * @api
     */
    public function addMembers(int $chatId, UserIdsList|RawData $userIds): void
    {
        $data = $this->httpClient->post("/chats/$chatId/members", $userIds);

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
     * Edit current bot info.
     *
     * Edits current bot info. Fill only the fields you want to update.
     * All remaining fields will stay untouched.
     *
     * @return BotInfo Modified bot info.
     * @api
     */
    public function editMyInfo(BotPatch $botPatch): BotInfo
    {
        $data = $this->httpClient->patch('/me', $botPatch);

        return BotInfo::newFromData($data);
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

    private function checkSimpleQueryResult(array $data): void
    {
        $result = SimpleQueryResult::newFromData($data);

        if (!$result->isSuccess()) {
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            throw new SimpleQueryError($result->getMessage() ?: 'The server did not return the message.');
        }
    }
}
