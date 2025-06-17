<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Models\Requests;

use MaxMessenger\Bot\Models\Enums\SenderAction;

/**
 * Request to send bot action to chat.
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
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    protected array $data;

    /**
     * @param SenderAction $action Bot action to send to chat.
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
     * @param SenderAction|null $action Bot action to send to chat.
     * @psalm-param SenderAction $action .
     * @noinspection PhpMissingParentCallCommonInspection
     * @api
     */
    public static function new(?SenderAction $action = null): static
    {
        static::validateNotNull('action', $action);

        return new static($action);
    }

    /**
     * @param SenderAction $action Bot action to send to chat.
     * @return $this
     * @api
     */
    public function setAction(SenderAction $action): static
    {
        $this->data['action'] = $action;

        return $this;
    }
}
