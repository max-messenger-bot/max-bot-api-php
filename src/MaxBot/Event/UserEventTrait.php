<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\MaxBot\Event;

/**
 * Методы событий, связанных с пользователем.
 *
 * Трейт больше ничего не добавляет: его методы разнесены по {@see SendMessageToChatTrait}
 * и {@see SendMessageToUserTrait}, которые события подключают самостоятельно.
 *
 * @psalm-require-extends BaseEvent
 * @deprecated Трейт будет удалён в следующих версиях. Он разделён на {@see SendMessageToChatTrait}
 *     и {@see SendMessageToUserTrait}.
 */
trait UserEventTrait {}
