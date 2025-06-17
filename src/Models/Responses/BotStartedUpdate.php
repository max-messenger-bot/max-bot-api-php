<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Bot gets this type of update as soon as user pressed `Start` button.
 *
 * @api
 */
class BotStartedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     payload?: string|null,
     *     user: array,
     *     user_locale?: string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int Dialog identifier where event has occurred.
     * @api
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return string|null Additional data from deep-link passed on bot startup (maxLength: 512).
     * @api
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return User User pressed the 'Start' button.
     * @api
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return string|null Current user locale in IETF BCP 47 format.
     * @api
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
