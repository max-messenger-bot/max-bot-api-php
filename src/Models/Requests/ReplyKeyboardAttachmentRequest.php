<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\AttachmentRequestType;

use function array_key_exists;

/**
 * Запрос на прикрепление клавиатуры к сообщению.
 */
final class ReplyKeyboardAttachmentRequest extends AttachmentRequest
{
    use ValidateTrait;
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     direct?: bool,
     *     direct_user_id?: int,
     *     buttons: non-empty-list<non-empty-list<ReplyButton>>
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param non-empty-array<non-empty-array<ReplyButton>>|null $buttons Двумерный массив кнопок (minItems: 1).
     * @param bool|null $direct Если `true`, клавиатура отображается только для пользователя,
     *     которому бот упомянул или ответил (применимо только для чатов).
     * @param int|null $directUserId Если установлено, клавиатура будет отображаться только для этого участника в чате.
     */
    public function __construct(
        ?array $buttons = null,
        ?bool $direct = null,
        ?int $directUserId = null
    ) {
        $this->required = ['buttons'];

        parent::__construct(AttachmentRequestType::ReplyKeyboard);

        if ($buttons !== null) {
            $this->setButtons($buttons);
        }
        if ($direct !== null) {
            $this->setDirect($direct);
        }
        if ($directUserId !== null) {
            $this->setDirectUserId($directUserId);
        }
    }

    /**
     * @return list<list<ReplyButton>>
     */
    public function getButtons(): array
    {
        return $this->data['buttons'];
    }

    public function getDirect(): ?bool
    {
        return $this->data['direct'] ?? null;
    }

    public function getDirectUserId(): ?int
    {
        return $this->data['direct_user_id'] ?? null;
    }

    public function issetButtons(): bool
    {
        return array_key_exists('buttons', $this->data);
    }

    public function issetDirect(): bool
    {
        return array_key_exists('direct', $this->data);
    }

    public function issetDirectUserId(): bool
    {
        return isset($this->data['direct_user_id']);
    }

    /**
     * @param non-empty-array<non-empty-array<ReplyButton>> $buttons Двумерный массив кнопок (minItems: 1).
     * @param bool|null $direct Если `true`, клавиатура отображается только для пользователя,
     *     которому бот упомянул или ответил (применимо только для чатов).
     * @param int|null $directUserId Если установлено, клавиатура будет отображаться только для этого участника в чате.
     */
    public static function make(
        array $buttons,
        ?bool $direct = null,
        ?int $directUserId = null
    ): self {
        return new self($buttons, $direct, $directUserId);
    }

    /**
     * @param non-empty-array<non-empty-array<ReplyButton>>|null $buttons Двумерный массив кнопок (minItems: 1).
     * @param bool|null $direct Если `true`, клавиатура отображается только для пользователя,
     *     которому бот упомянул или ответил (применимо только для чатов).
     * @param int|null $directUserId Если установлено, клавиатура будет отображаться только для этого участника в чате.
     */
    public static function new(
        ?array $buttons = null,
        ?bool $direct = null,
        ?int $directUserId = null
    ): self {
        return new self($buttons, $direct, $directUserId);
    }

    /**
     * @param non-empty-array<non-empty-array<ReplyButton>> $buttons Двумерный массив кнопок (minItems: 1).
     * @return $this
     */
    public function setButtons(array $buttons): self
    {
        self::validateArray('buttons', $buttons, minItems: 1);
        self::validateArray2D('buttons', $buttons, minItems: 1);

        foreach ($buttons as &$button) {
            $button = array_values($button);
        }
        /** @var non-empty-array<non-empty-list<ReplyButton>> $buttons */

        $this->data['buttons'] = array_values($buttons);
        $this->data['payload']['buttons'] = array_values($buttons);

        return $this;
    }

    /**
     * @param bool $direct Если `true`, клавиатура отображается только для пользователя,
     *     которому бот упомянул или ответил (применимо только для чатов).
     * @return $this
     */
    public function setDirect(bool $direct): self
    {
        $this->data['direct'] = $direct;

        return $this;
    }

    /**
     * @param int $directUserId Если установлено, клавиатура будет отображаться только для этого участника в чате.
     * @return $this
     */
    public function setDirectUserId(int $directUserId): self
    {
        $this->data['direct_user_id'] = $directUserId;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetDirect(): self
    {
        unset($this->data['direct']);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetDirectUserId(): self
    {
        unset($this->data['direct_user_id']);

        return $this;
    }
}
