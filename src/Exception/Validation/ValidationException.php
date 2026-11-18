<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

use MaxMessenger\Bot\Exception\MaxApiException;

/**
 * Ошибка проверки данных.
 *
 * Базовый класс исключений проверки данных в клиенте API МАКС.
 */
abstract class ValidationException extends MaxApiException {}
