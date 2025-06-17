<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

use MaxMessenger\Api\Old\Models\Enums\UpdateType;
use MaxMessenger\Api\Old\Models\Response\Subscriptions\Update;

/**
 * @api
 */
final class Other extends Update
{
    /**
     * Необработанный тип события.
     */
    public function getRawUpdateType(): ?string
    {
        /** @var string|null */
        return $this->raw['update_type'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateType(): UpdateType
    {
        return ($this->raw['update_type'] ?? null) === null
            ? UpdateType::NotSet
            : UpdateType::Other;
    }
}
