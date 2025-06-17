<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\SenderAction;

/**
 * Запрос на отправку действия бота в чат.
 *
 * @api
 */
final class ActionRequestBody extends BaseRequestModel
{
    use ValidateTrait;

    /**
     * @var array{
     *     action: SenderAction
     * }
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected array $data = [];

    /**
     * @param SenderAction $action Действие, отправляемое участникам чата.
     * @api
     */
    public function __construct(SenderAction $action)
    {
        $this->setAction($action);
    }

    /**
     * @api
     */
    public function getAction(): SenderAction
    {
        return $this->data['action'];
    }

    /**
     * @param SenderAction|null $action Действие, отправляемое участникам чата.
     * @psalm-param SenderAction $action
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?SenderAction $action = null): static
    {
        static::validateNotNull('action', $action);

        return new static($action);
    }

    /**
     * @param SenderAction $action Действие, отправляемое участникам чата.
     * @return $this
     * @api
     */
    public function setAction(SenderAction $action): static
    {
        $this->data['action'] = $action;

        return $this;
    }
}
