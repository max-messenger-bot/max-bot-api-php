<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\MaxBot\Update;

use MaxMessenger\Bot\Exception\MaxApiException;

/**
 * Ошибка запроса с обновлением.
 *
 * Базовый класс исключений, связанных с обработкой обновлений.
 */
abstract class UpdateRequestException extends MaxApiException {}
