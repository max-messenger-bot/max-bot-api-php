<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules\Bots\Requests;

use MaxMessenger\Api\Modules\BasePostRequestModel;

final class Command extends BasePostRequestModel
{
    /**
     * @param non-empty-string $name
     * @param non-empty-string|null $description
     */
    public function __construct(string $name, ?string $description = null)
    {
        $this->setName($name);
        if ($description !== null) {
            $this->setDescription($description);
        }
    }

    /**
     * Описание команды.
     * От 1 до 128 символов.
     *
     * @param non-empty-string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        if ($description !== null) {
            $this->data['description'] = $description;
        } else {
            unset($this->data['description']);
        }

        return $this;
    }

    /**
     * Название команды.
     * От 1 до 64 символов.
     *
     * @param non-empty-string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->data['name'] = $name;

        return $this;
    }
}
