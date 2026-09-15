<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

use MaxMessenger\Bot\Model\Response\ContactAttachment;
use MaxMessenger\Bot\Model\Response\Message;
use MaxMessenger\Bot\Model\Response\MessageCreatedUpdate;

use function count;

/**
 * Событие создания сообщения.
 *
 * Событие может относиться к объекту, который не поддерживается MAX API, — тогда сообщения в нём нет.
 * В этом случае {@see getMessage()} и методы трейта, которым нужно сообщение ({@see MessageEventTrait::reply()},
 * {@see MessageEventTrait::deleteMessage()} и другие), прерывают обработку события через {@see BaseEvent::continue()}.
 * Проверить наличие сообщения заранее можно методом {@see hasMessage()}.
 *
 * На свои действия бот события не получает.
 *
 * @property-read MessageCreatedUpdate $update
 */
final class MessageCreatedEvent extends BaseEvent
{
    use MessageEventTrait;

    /**
     * Новое созданное сообщение.
     *
     * Если событие относится к объекту, который не поддерживается MAX API,
     * сообщения в нём нет и обработка события прерывается через {@see BaseEvent::continue()}.
     *
     * @return Message Новое созданное сообщение.
     */
    public function getMessage(): Message
    {
        return $this->update->getMessage() ?? $this->continue();
    }

    /**
     * @return string|null Текущий язык пользователя в формате IETF BCP 47. Доступно только в диалогах.
     */
    public function getUserLocale(): ?string
    {
        return $this->update->getUserLocale();
    }

    /**
     * @return bool `true`, если событие содержит сообщение.
     */
    public function hasMessage(): bool
    {
        return $this->update->getMessage() !== null;
    }

    /**
     * Проверяет, содержит ли сообщение реальные контактные данные отправителя.
     *
     * Позволяет проверить, что пользователь поделился номером телефона, привязанным к его аккаунту в МАКС.
     *
     * Если проверка прошла успешно, Вы можете получить номер телефона следующим способом:
     * ```
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
