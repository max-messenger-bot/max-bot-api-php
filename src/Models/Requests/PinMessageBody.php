<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use function array_key_exists;

/**
 * Запрос на закрепление сообщения в чате или канале.
 */
final class PinMessageBody extends BaseRequestModel
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     message_id: non-empty-string,
     *     notify: bool
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-string|null $messageId ID сообщения, которое нужно закрепить (minLength: 1).
     *     Соответствует полю `Message.body.mid`.
     * @param bool $notify Если `true`, участники получат уведомление с системным сообщением о закреплении.
     */
    public function __construct(?string $messageId = null, bool $notify = true)
    {
        $this->required = ['message_id'];

        if ($messageId !== null) {
            $this->setMessageId($messageId);
        }
        $this->setNotify($notify);
    }

    /**
     * @return non-empty-string
     */
    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    public function getNotify(): bool
    {
        return $this->data['notify'];
    }

    public function issetMessageId(): bool
    {
        return array_key_exists('message_id', $this->data);
    }

    /**
     * @param non-empty-string $messageId ID сообщения, которое нужно закрепить (minLength: 1).
     *     Соответствует полю `Message.body.mid`.
     * @param bool $notify Если `true`, участники получат уведомление с системным сообщением о закреплении.
     */
    public static function make(string $messageId, bool $notify = true): self
    {
        return new self($messageId, $notify);
    }

    /**
     * @param non-empty-string|null $messageId ID сообщения, которое нужно закрепить (minLength: 1).
     *     Соответствует полю `Message.body.mid`.
     * @param bool $notify Если `true`, участники получат уведомление с системным сообщением о закреплении.
     */
    public static function new(?string $messageId = null, bool $notify = true): self
    {
        return new self($messageId, $notify);
    }

    /**
     * @param non-empty-string $messageId ID сообщения, которое нужно закрепить (minLength: 1).
     *     Соответствует полю `Message.body.mid`.
     * @return $this
     */
    public function setMessageId(string $messageId): self
    {
        self::validateString('messageId', $messageId, minLength: 1);

        $this->data['message_id'] = $messageId;

        return $this;
    }

    /**
     * @param bool $notify Если `true`, участники получат уведомление с системным сообщением о закреплении.
     * @return $this
     */
    public function setNotify(bool $notify): self
    {
        $this->data['notify'] = $notify;

        return $this;
    }
}
