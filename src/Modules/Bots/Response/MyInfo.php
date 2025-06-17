<?php

declare(strict_types=1);

namespace MaxMessenger\Api\Modules\Bots\Response;

use DateTimeInterface;
use MaxMessenger\Api\Modules\BaseResponseModel;

final class MyInfo extends BaseResponseModel
{
    /**
     * Возвращает URL аватара.
     *
     * @return non-empty-string|null
     */
    public function getAvatarUrl(): ?string
    {
        return $this->raw['avatar_url'] ?? null;
    }

    /**
     * Возвращает URL аватара большого размера.
     *
     * @return Command[]|null
     */
    public function getCommands(): ?array
    {
        $commands = $this->raw['commands'];

        if ($commands !== null) {
            foreach ($commands as &$command) {
                $command = new Command($command);
            }
        }

        return $commands;
    }

    /**
     * Возвращает описание пользователя (до 16000 символов).
     * Может быть `null`, если пользователь его не заполнил.
     *
     * @return non-empty-string|null
     */
    public function getDescription(): ?string
    {
        return $this->raw['description'] ?? null;
    }

    /**
     * Возвращает отображаемое имя пользователя.
     *
     * @return non-empty-string
     */
    public function getFirstName(): string
    {
        return $this->raw['first_name'];
    }

    /**
     * Возвращает URL аватара большого размера.
     *
     * @return non-empty-string|null
     */
    public function getFullAvatarUrl(): ?string
    {
        return $this->raw['full_avatar_url'] ?? null;
    }

    /**
     * Возвращает время последней активности пользователя в MAX.
     * Может быть неактуальным, если пользователь отключил статус "онлайн" в настройках.
     */
    public function getLastActivityTime(): DateTimeInterface
    {
        return $this->createDateTimeFromTimestamp($this->raw['last_activity_time']);
    }

    /**
     * Возвращает отображаемую фамилия пользователя.
     *
     * @return non-empty-string|null
     */
    public function getLastName(): ?string
    {
        return $this->raw['last_name'] ?? null;
    }

    /**
     * Возвращает ID пользователя.
     */
    public function getUserId(): int
    {
        return $this->raw['user_id'];
    }

    /**
     * Возвращает уникальное публичное имя пользователя.
     * Может быть `null`, если пользователь недоступен или имя не задано.
     *
     * @return non-empty-string|null
     */
    public function getUsername(): ?string
    {
        return $this->raw['username'] ?? null;
    }

    /**
     * Возвращает `true`, если пользователь является ботом.
     */
    public function isBot(): bool
    {
        return $this->raw['is_bot'];
    }
}
