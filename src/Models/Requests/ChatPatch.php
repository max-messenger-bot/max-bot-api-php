<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\MaxApiClient;

use function array_key_exists;

/**
 * Request to edit chat info.
 *
 * @api
 */
final class ChatPatch extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     icon?: PhotoAttachmentRequestPayload|null,
     *     notify: bool,
     *     pin?: non-empty-string|null,
     *     title?: non-empty-string|null
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Request to set chat icon.
     * @param non-empty-string|null $title Chat title (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $pin Identifier of message to be pinned in chat (minLength: 1).
     *     In case you want to remove pin, use {@see MaxApiClient::unpinMessage()}) method.
     * @param bool $notify By default, participants will be notified about change with system message in chat/channel.
     * @api
     */
    public function __construct(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $pin = null,
        bool $notify = true
    ) {
        if ($icon !== null) {
            $this->setIcon($icon);
        }
        if ($title !== null) {
            $this->setTitle($title);
        }
        if ($pin !== null) {
            $this->setPin($pin);
        }
        $this->setNotify($notify);
    }

    /**
     * @api
     */
    public function getIcon(): ?PhotoAttachmentRequestPayload
    {
        return $this->data['icon'] ?? null;
    }

    /**
     * @api
     */
    public function getNotify(): bool
    {
        return $this->data['notify'];
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getPin(): ?string
    {
        return $this->data['pin'] ?? null;
    }

    /**
     * @return non-empty-string|null
     * @api
     */
    public function getTitle(): ?string
    {
        return $this->data['title'] ?? null;
    }

    /**
     * @api
     */
    public function issetIcon(): bool
    {
        return array_key_exists('icon', $this->data);
    }

    /**
     * @api
     */
    public function issetPin(): bool
    {
        return array_key_exists('pin', $this->data);
    }

    /**
     * @api
     */
    public function issetTitle(): bool
    {
        return array_key_exists('title', $this->data);
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Request to set chat icon.
     * @param non-empty-string|null $title Chat title (minLength: 1, maxLength: 200).
     * @param non-empty-string|null $pin Identifier of message to be pinned in chat
     *     (minLength: 1).
     * @param bool $notify By default, participants will be notified about change
     *     with system message in chat/channel.
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(
        ?PhotoAttachmentRequestPayload $icon = null,
        ?string $title = null,
        ?string $pin = null,
        bool $notify = true
    ): static {
        return new static($icon, $title, $pin, $notify);
    }

    /**
     * @param PhotoAttachmentRequestPayload|null $icon Request to set chat icon.
     * @api
     */
    public function setIcon(?PhotoAttachmentRequestPayload $icon = null): static
    {
        $this->data['icon'] = $icon;

        return $this;
    }

    /**
     * @param bool $notify By default, participants will be notified about change
     *     with system message in chat/channel.
     * @api
     */
    public function setNotify(bool $notify): static
    {
        $this->data['notify'] = $notify;

        return $this;
    }

    /**
     * @param non-empty-string|null $pin Identifier of message to be pinned in chat (minLength: 1).
     * @api
     */
    public function setPin(?string $pin = null): static
    {
        static::validateString('pin', $pin, minLength: 1);

        $this->data['pin'] = $pin;

        return $this;
    }

    /**
     * @param non-empty-string|null $title Chat title (minLength: 1, maxLength: 200).
     * @api
     */
    public function setTitle(?string $title = null): static
    {
        static::validateString('title', $title, minLength: 1, maxLength: 200);

        $this->data['title'] = $title;

        return $this;
    }

    /**
     * @api
     */
    public function unsetIcon(): static
    {
        unset($this->data['icon']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetNotify(): static
    {
        unset($this->data['notify']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetPin(): static
    {
        unset($this->data['pin']);

        return $this;
    }

    /**
     * @api
     */
    public function unsetTitle(): static
    {
        unset($this->data['title']);

        return $this;
    }
}
