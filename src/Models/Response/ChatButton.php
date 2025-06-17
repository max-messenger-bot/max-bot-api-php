<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Response;

/**
 * Chat button.
 *
 * Button that creates new chat as soon as the first user clicked on it.
 * Bot will be added to chat participants as administrator.
 * Message author will be owner of the chat.
 *
 * @api
 */
readonly class ChatButton extends Button
{
    /**
     * @var array{
     *     chat_title: string,
     *     chat_description?: string|null,
     *     start_payload?: string|null,
     *     uuid?: int|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @return string|null Chat description (maxLength: 400).
     * @api
     */
    public function getChatDescription(): ?string
    {
        return $this->data['chat_description'] ?? null;
    }

    /**
     * @return string Title of chat to be created (maxLength: 200).
     * @api
     */
    public function getChatTitle(): string
    {
        return $this->data['chat_title'];
    }

    /**
     * @return string|null Start payload will be sent to bot as soon as chat created (maxLength: 512).
     * @api
     */
    public function getStartPayload(): ?string
    {
        return $this->data['start_payload'] ?? null;
    }

    /**
     * @return int|null Unique button identifier across all chat buttons in keyboard.
     *     If `uuid` changed, new chat will be created on the next click.
     *     Server will generate it at the time when button initially posted.
     *     Reuse it when you edit the message.
     * @api
     */
    public function getUuid(): ?int
    {
        return $this->data['uuid'] ?? null;
    }
}
