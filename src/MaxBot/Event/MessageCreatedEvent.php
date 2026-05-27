<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\ContactAttachment;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageCreatedUpdate;

use function count;

/**
 * @property-read MessageCreatedUpdate $update
 */
final class MessageCreatedEvent extends BaseEvent
{
    use MessageEventTrait;

    /**
     * @return Message Новое созданное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage();
    }

    /**
     * @return string|null Текущий язык пользователя в формате IETF BCP 47. Доступно только в диалогах.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }

    /**
     * Проверяет, содержит ли сообщение реальные контактные данные отправителя.
     *
     * Позволяет проверить, что пользователь поделился номером телефона, привязанным к его аккаунту в МАКС.
     *
     * Если проверка прошла успешно, Вы можете получить номер телефона следующим способом:
     * ```
     * /** @var ContactAttachment $contact *\/
     * $contact = $event->getMessage()->getBody()->getAttachments()[0];
     * $phones = $contact->getPayload()->getPhones();
     * ```
     */
    public function isSelfContact(): bool
    {
        $attachments = $this->getMessage()->getBody()->getAttachments();

        if ($attachments !== null && count($attachments) === 1) {
            $attachment = $attachments[0];

            if ($attachment instanceof ContactAttachment) {
                $payload = $attachment->getPayload();

                return $this->apiClient->validateContactAttachmentHash($payload);
            }
        }

        return false;
    }
}
