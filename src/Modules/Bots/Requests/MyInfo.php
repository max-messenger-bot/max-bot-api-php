<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules\Bots\Requests;

use MaxMessenger\Api\Modules\BasePostRequestModel;

final class MyInfo extends BasePostRequestModel
{
    /**
     * Обновить отображаемое имя бота.
     * От 1 до 64 символов.
     *
     * @param Command[]|null $commands
     * @return $this
     */
    public function setCommands(?array $commands): self
    {
        $this->data['commands'] = $commands;

        return $this;
    }

    /**
     * Обновить описание бота.
     * От 1 до 16000 символов.
     *
     * @param non-empty-string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->data['description'] = $description;

        return $this;
    }

    /**
     * Обновить отображаемое имя бота.
     * От 1 до 64 символов.
     *
     * @param non-empty-string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): self
    {
        $this->data['first_name'] = $firstName;

        return $this;
    }

    /**
     * Обновить отображаемое второе имя бота.
     * От 1 до 64 символов.
     *
     * @param non-empty-string|null $lastName
     * @return $this
     */
    public function setLastName(?string $lastName): self
    {
        $this->data['last_name'] = $lastName;

        return $this;
    }
}
