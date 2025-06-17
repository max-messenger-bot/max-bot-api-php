<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules;

use MaxMessenger\Api\Modules\Bots\Requests\MyInfo;
use MaxMessenger\Api\Modules\Bots\Response\MyInfo as MyInfoResponse;

final class Bots
{
    use ModuleTrait;

    public function getMyInfo(): MyInfoResponse
    {
        return new MyInfoResponse($this->client->get('/me'));
    }

    public function updateMyInfo(MyInfo $myInfo): MyInfoResponse
    {
        return new MyInfoResponse($this->client->patch('/me', $myInfo));
    }
}
