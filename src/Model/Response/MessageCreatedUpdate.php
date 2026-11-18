<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;

/**
 * Событие создания сообщения.
 *
 * В групповом чате бот получает событие, только если он администратор с правом
 * {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @link https://dev.max.ru/docs-api/objects/Update/
 * @link https://dev.max.ru/docs-api/methods/GET/updates/
 */
class MessageCreatedUpdate extends Update
{
    /**
     * @var array{
     *     message: array,
     *     user_locale?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private Message|false $message = false;

    /**
     * @return Message Новое созданное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->message === false
            ? $this->message = Message::newFromData($this->data['message'])
            : $this->message;
    }

    /**
     * @return list<non-empty-string> Имена обязательных свойств обновления.
     */
    public function getRequiredProperties(): array
    {
        return ['message'];
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47. Доступно только в диалогах.
     */
    public function getUserLocale(): ?string
    {
        return $this->data['user_locale'] ?? null;
    }
}
