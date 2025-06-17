<?php

declare(strict_types=1);

namespace MaxMessenger\Api;

use MaxMessenger\Api\Contracts\MaxBotConfigInterface;
use MaxMessenger\Api\Modules\Bots;
use MaxMessenger\Api\Modules\Bots\Requests\MyInfo;
use MaxMessenger\Api\Modules\Bots\Response\MyInfo as MyInfoResponse;

use function is_object;

/**
 * @api
 */
final readonly class MaxBot
{
    private MaxBotRawClient $client;

    public function __construct(
        string|MaxBotConfigInterface|null $accessTokenOrConfig = null
    ) {
        $config = is_object($accessTokenOrConfig)
            ? $accessTokenOrConfig
            : new MaxBotConfig($accessTokenOrConfig);

        $this->client = new MaxBotRawClient($config);
    }

    public function getMyInfo(): MyInfoResponse
    {
        return $this->bots()->getMyInfo();
    }

    public function updateMyInfo(MyInfo $myInfo): MyInfoResponse
    {
        return $this->bots()->updateMyInfo($myInfo);
    }

    private function bots(): Bots
    {
        return new Bots($this->client);
    }

//    private function chats(): MaxChats
//    {
//        return new MaxChats($this->config);
//    }
//
//    private function messages(): MaxMessages
//    {
//        return new MaxMessages($this->config);
//    }
//
//    private function subscriptions(): MaxSubscriptions
//    {
//        return new MaxSubscriptions($this->config);
//    }
//
//    private function upload(): MaxUpload
//    {
//        return new MaxUpload($this->config);
//    }
}
