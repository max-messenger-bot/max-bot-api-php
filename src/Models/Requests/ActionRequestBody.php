<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\SenderAction;

use function array_key_exists;

/**
 * Запрос на отправку действия бота в чат.
 */
final class ActionRequestBody extends BaseRequestModel
{
    use ValidateRequiredTrait;

    /**
     * @var array{
     *     action: SenderAction
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType, InvalidPropertyAssignmentValue
     */
    protected array $data = [];

    /**
     * @param SenderAction|null $action Действие, отправляемое участникам чата.
     */
    public function __construct(?SenderAction $action = null)
    {
        $this->required = ['action'];

        if ($action !== null) {
            $this->setAction($action);
        }
    }

    public function getAction(): SenderAction
    {
        return $this->data['action'];
    }

    public function issetAction(): bool
    {
        return array_key_exists('action', $this->data);
    }

    /**
     * @param SenderAction $action Действие, отправляемое участникам чата.
     */
    public static function make(SenderAction $action): self
    {
        return new self($action);
    }

    /**
     * @param SenderAction|null $action Действие, отправляемое участникам чата.
     */
    public static function new(?SenderAction $action = null): self
    {
        return new self($action);
    }

    /**
     * @param SenderAction $action Действие, отправляемое участникам чата.
     * @return $this
     */
    public function setAction(SenderAction $action): self
    {
        $this->data['action'] = $action;

        return $this;
    }
}
