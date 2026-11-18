<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Событие начала или возобновления общения пользователя с ботом (нажатия кнопки "Начать").
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class BotStartedUpdate extends Update
{
    /**
     * @var array{
     *     chat_id: int,
     *     user: array,
     *     payload?: non-empty-string,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private User|false $user = false;

    /**
     * @return int Идентификатор диалога, где произошло событие.
     */
    public function getChatId(): int
    {
        return $this->data['chat_id'];
    }

    /**
     * @return non-empty-string|null Дополнительные данные из диплинков, переданные при запуске бота
     *     (maxLength: 128).
     *     Подробнее о диплинках — {@link https://dev.max.ru/help/deeplinks в разделе FAQ}.
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['chat_id', 'user'];
    }

    /**
     * @return User Пользователь, который запустил бота через интерфейс, например нажав кнопку «Начать».
     */
    public function getUser(): User
    {
        return $this->user === false
            ? $this->user = User::newFromData($this->data['user'])
            : $this->user;
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47.
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
