<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Enum\ChatAdminPermission;
use MaxMessenger\Bot\Model\Response\ContactAttachment;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageCreatedUpdate;

/**
 * Событие создания сообщения.
 *
 * В групповом чате бот получает событие, только если он администратор с правом
 * {@see ChatAdminPermission::ReadAllMessages}.
 *
 * @property-read MessageCreatedUpdate $update
 */
final class MessageCreatedEvent extends BaseEvent
{
    use MessageEventTrait;

    private ContactAttachment|false|null $contactAttachment = false;

    /**
     * @return ContactAttachment|null Контакт среди вложений сообщения.
     */
    public function getContactAttachment(): ?ContactAttachment
    {
        if ($this->contactAttachment !== false) {
            return $this->contactAttachment;
        }

        foreach ($this->getMessage()->getBody()->getAttachments() ?? [] as $attachment) {
            if ($attachment instanceof ContactAttachment) {
                return $this->contactAttachment = $attachment;
            }
        }

        return $this->contactAttachment = null;
    }

    /**
     * @return Message Новое созданное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage();
    }

    /**
     * @return non-empty-string|null Текущий язык пользователя в формате IETF BCP 47. Доступно только в диалогах.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }

    /**
     * @return bool `true`, если сообщение содержит контакт.
     */
    public function isContact(): bool
    {
        return $this->getContactAttachment() !== null;
    }

    /**
     * Проверяет, содержит ли сообщение реальные контактные данные отправителя.
     *
     * Позволяет проверить, что пользователь поделился номером телефона, привязанным к его профилю в МАКС.
     *
     * Если проверка прошла успешно, Вы можете получить номер телефона следующим способом:
     * ```
     * $contact = $event->getContactAttachment();
     * if ($contact !== null && $event->isSelfContact()) {
     *     $phones = $contact->getPayload()->getPhones();
     * }
     * ```
     *
     * @return bool `true`, если контакт содержит номер телефона отправителя, привязанный к его профилю в МАКС.
     */
    public function isSelfContact(): bool
    {
        $contact = $this->getContactAttachment();

        return $contact !== null && $this->apiClient->validateContactAttachmentHash($contact->getPayload());
    }
}
