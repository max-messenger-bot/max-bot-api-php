<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Event;

use MaxMessenger\Bot\Exception\MaxApiLogicException;

/**
 * Идентификатор поста отсутствует.
 *
 * Исключение выбрасывается, когда в комментарии нет идентификатора поста, к которому он оставлен.
 */
final class PostIdMissingException extends MaxApiLogicException
{
    public function __construct()
    {
        parent::__construct('Post id missing.');
    }
}
