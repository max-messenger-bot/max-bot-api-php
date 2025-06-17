<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

use DateTimeImmutable;

/**
 * @api
 */
readonly class User extends BaseResponseModel
{
    /**
     * @var array{
     *     first_name: string,
     *     is_bot: bool,
     *     last_activity_time: int,
     *     last_name: string|null,
     *     name?: string|null,
     *     user_id: int,
     *     username: string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string Users first name.
     * @api
     */
    public function getFirstName(): string
    {
        return $this->data['first_name'];
    }

    /**
     * @return DateTimeImmutable Time of last user activity in Max.
     *     Can be outdated if user disabled its "online" status in settings.
     * @api
     */
    public function getLastActivityTime(): DateTimeImmutable
    {
        return static::makeDateTime($this->data['last_activity_time']);
    }

    /**
     * @return int Time of last user activity in Max (Unix timestamp in milliseconds).
     *     Can be outdated if user disabled its "online" status in settings.
     * @api
     */
    public function getLastActivityTimeRaw(): int
    {
        return $this->data['last_activity_time'];
    }

    /**
     * @return string|null Users last name.
     * @api
     */
    public function getLastName(): ?string
    {
        return $this->data['last_name'];
    }

    /**
     * @return string|null Visible display name of user or bot.
     * @api
     */
    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * @return int Users identifier.
     * @api
     */
    public function getUserId(): int
    {
        return $this->data['user_id'];
    }

    /**
     * @return string|null Unique public user name. Can be `null` if user is not accessible or it is not set.
     * @api
     */
    public function getUsername(): ?string
    {
        return $this->data['username'];
    }

    /**
     * @return bool `true` if user is bot.
     * @api
     */
    public function isBot(): bool
    {
        return $this->data['is_bot'];
    }
}
