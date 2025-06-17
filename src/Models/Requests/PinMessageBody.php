<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Запрос на закрепление сообщения в чате или канале.
 *
 * @api
 */
final class PinMessageBody extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     message_id: non-empty-string,
     *     notify: bool
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param non-empty-string $messageId ID сообщения, которое нужно закрепить. Соответствует полю `Message.body.mid`.
     * @param bool $notify Если `true`, участники получат уведомление с системным сообщением о закреплении.
     * @api
     */
    public function __construct(string $messageId, bool $notify = true)
    {
        $this->setMessageId($messageId);
        $this->setNotify($notify);
    }

    /**
     * @return non-empty-string
     * @api
     */
    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    /**
     * @api
     */
    public function getNotify(): bool
    {
        return $this->data['notify'];
    }

    /**
     * @api
     */
    public function issetNotify(): bool
    {
        return array_key_exists('notify', $this->data);
    }

    /**
     * @param non-empty-string|null $messageId ID сообщения, которое нужно закрепить.
     *     Соответствует полю `Message.body.mid`.
     * @psalm-param non-empty-string $messageId
     * @param bool $notify Если `true`, участники получат уведомление с системным сообщением о закреплении.
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(string $messageId = null, bool $notify = true): static
    {
        static::validateNotNull('messageId', $messageId);

        return new static($messageId, $notify);
    }

    /**
     * @param non-empty-string $messageId ID сообщения, которое нужно закрепить. Соответствует полю `Message.body.mid`.
     * @return $this
     * @api
     */
    public function setMessageId(string $messageId): static
    {
        $this->data['message_id'] = $messageId;

        return $this;
    }

    /**
     * @param bool $notify Если `true`, участники получат уведомление с системным сообщением о закреплении.
     * @return $this
     * @api
     */
    public function setNotify(bool $notify): static
    {
        $this->data['notify'] = $notify;

        return $this;
    }
}
