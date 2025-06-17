<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Bot gets this type of update as soon as user stopped the bot.
 *
 * @api
 */
class BotStoppedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
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
     * @return User User who stopped the bot.
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
