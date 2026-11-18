<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Response;

/**
 * Кнопка запроса контакта.
 *
 * После нажатия на такую кнопку клиент отправляет новое сообщение с вложением текущего контакта пользователя.
 *
 * @link https://dev.max.ru/docs-api/objects/Message/
 * @link https://dev.max.ru/docs-api/use-cases/sending-messages/keyboard
 */
class RequestContactButton extends Button {}
