<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exceptions;

final class EventException extends LogicException
{
    public function __construct(int $code)
    {
        parent::__construct('Event Internal Exception', $code);
    }

    public static function break(): never
    {
        throw new self(1);
    }

    public static function continue(): never
    {
        throw new self(0);
    }

    public function isBreak(): bool
    {
        return $this->code === 1;
    }
}
