<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules\Bots\Response;

use MaxMessenger\Api\Modules\BaseResponseModel;

final class Command extends BaseResponseModel
{
    /**
     * Возвращает описание команды (от 1 до 128 символов).
     *
     * @return non-empty-string|null
     */
    public function getDescription(): ?string
    {
        return $this->raw['description'] ?? null;
    }

    /**
     * Возвращает название команды (от 1 до 64 символов).
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->raw['name'];
    }
}
