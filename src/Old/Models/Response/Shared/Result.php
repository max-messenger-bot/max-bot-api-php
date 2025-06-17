<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Shared;

use MaxMessenger\Api\Modules\BaseResponseModel;

/**
 * @api
 */
final class Result extends BaseResponseModel
{
    /**
     * Объяснительное сообщение, если результат не был успешным.
     */
    public function getMessage(): ?string
    {
        /** @var string|null */
        return $this->raw['message'] ?? null;
    }

    /**
     * `true`, если запрос был успешным, `false` в противном случае.
     */
    public function isSuccess(): bool
    {
        /** @var bool */
        return $this->raw['success'];
    }
}
