<?php

namespace MaxMessenger\Bot\Contracts;

use MaxMessenger\Bot\MaxBot;

interface CommandClassInterface
{
    public function handle(mixed $command, MaxBot $maxBot): mixed;
}
