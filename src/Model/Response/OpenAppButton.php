<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Кнопка для запуска мини-приложения.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
class OpenAppButton extends Button
{
    /**
     * @var array{
     *     web_app?: non-empty-string,
     *     contact_id?: int,
     *     payload?: non-empty-string
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;

    /**
     * @return int|null Идентификатор бота, чьё мини-приложение надо запустить.
     */
    public function getContactId(): ?int
    {
        return $this->data['contact_id'] ?? null;
    }

    /**
     * @return non-empty-string|null Параметр запуска, который будет передан в `initData` мини-приложения
     *     (maxLength: 512, pattern: '^[\w-]*$').
     */
    public function getPayload(): ?string
    {
        return $this->data['payload'] ?? null;
    }

    /**
     * @return non-empty-string|null Публичное имя (username) бота, чьё мини-приложение надо запустить.
     */
    public function getWebApp(): ?string
    {
        return $this->data['web_app'] ?? null;
    }
}
