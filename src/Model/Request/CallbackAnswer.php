<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use function array_key_exists;

/**
 * Отправьте этот объект, когда ваш бот хочет отреагировать на нажатие кнопки.
 */
final class CallbackAnswer extends BaseRequestModel
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     message: NewMessageBody
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     */
    public function __construct(?NewMessageBody $message = null)
    {
        $this->required = ['message'];

        if ($message !== null) {
            $this->setMessage($message);
        }
    }

    public function getMessage(): ?NewMessageBody
    {
        return $this->data['message'] ?? null;
    }

    public function issetMessage(): bool
    {
        return array_key_exists('message', $this->data);
    }

    /**
     * @param NewMessageBody $message Данные для обновления текущего сообщения.
     */
    public static function make(NewMessageBody $message): self
    {
        return new self($message);
    }

    /**
     * @param NewMessageBody|null $message Данные для обновления текущего сообщения.
     */
    public static function new(?NewMessageBody $message = null): self
    {
        return new self($message);
    }

    /**
     * @param NewMessageBody $message Данные для обновления текущего сообщения.
     * @return $this
     */
    public function setMessage(NewMessageBody $message): self
    {
        $this->data['message'] = $message;

        return $this;
    }
}
