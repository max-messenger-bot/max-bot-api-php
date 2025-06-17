<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Responses;

/**
 * Бот получает этот тип обновления, как только пользователь нажал кнопку `Start`.
 */
class BotStartedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     payload?: string,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int ID диалога, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return string|null Дополнительные данные из дип-линков, переданные при запуске бота (maxLength: 512).
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return User Пользователь, который нажал кнопку 'Start'.
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47 (minLength: 1).
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
