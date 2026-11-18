<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

use MaxMessenger\Bot\Model\Enum\MessageLinkType;

use function filter_var;
use function is_int;
use function is_string;

use const FILTER_VALIDATE_INT;

/**
 * Связанное сообщение, на которое отвечают или которое пересылают.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 */
class LinkedMessage extends BaseResponseModel
{
    /**
     * @var array{
     *     type: non-empty-string,
     *     sender?: array|string|int,
     *     chat_id: int,
     *     message: array
     * }
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected readonly array $data;
    private MessageBody|false $message = false;
    private User|false|null $sender = false;

    /**
     * @return int|null Идентификатор чата или канала, в котором опубликовано связанное сообщение.
     *     Для {@see isForward()} это чат-источник, для {@see isReply()} — чат самого сообщения.
     *     `null`, если у бота нет доступа к чату-источнику.
     */
    public function getChatId(): ?int
    {
        /**
         * Сервер передаёт `0`, если у бота нет доступа к чату-источнику.
         *
         * @psalm-suppress RiskyTruthyFalsyComparison Поле объявлено обязательным, но проверяем и его отсутствие.
         */
        return $this->data['chat_id'] ?? null ?: null;
    }

    /**
     * @return MessageBody Информация о связанном сообщении.
     */
    public function getMessage(): MessageBody
    {
        return $this->message === false
            ? ($this->message = MessageBody::newFromData($this->data['message']))
            : $this->message;
    }

    /**
     * @return User|null Пользователь или бот, отправивший сообщение.
     *     Может быть `null`, если сообщение опубликовано от имени канала.
     *     Если API вернул только идентификатор, имя будет `**HIDDEN**`, а `is_bot` — `false`.
     */
    public function getSender(): ?User
    {
        if ($this->sender !== false) {
            return $this->sender;
        }

        $sender = $this->data['sender'] ?? null;
        if (is_string($sender) || is_int($sender)) {
            $senderId = filter_var($sender, FILTER_VALIDATE_INT);
            $sender = is_int($senderId) && $senderId !== 0
                ? ['user_id' => $senderId, 'first_name' => '**HIDDEN**', 'is_bot' => false]
                : null;
        }

        return $this->sender = User::newFromNullableData($sender);
    }

    /**
     * @return MessageLinkType|null Тип связанного сообщения.
     */
    public function getType(): ?MessageLinkType
    {
        return MessageLinkType::tryFrom($this->data['type']);
    }

    /**
     * @return non-empty-string Тип связанного сообщения. Значения описаны в {@see MessageLinkType}.
     */
    public function getTypeRaw(): string
    {
        return $this->data['type'];
    }

    /**
     * @return bool Является ли связанное сообщение пересланным.
     */
    public function isForward(): bool
    {
        return $this->data['type'] === MessageLinkType::Forward->value;
    }

    /**
     * @return bool Является ли связанное сообщение ответом.
     */
    public function isReply(): bool
    {
        return $this->data['type'] === MessageLinkType::Reply->value;
    }
}
