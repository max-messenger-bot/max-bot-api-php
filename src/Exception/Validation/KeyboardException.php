<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception\Validation;

/**
 * Ошибка проверки клавиатуры.
 *
 * Возникает, если конфигурация клавиатуры нарушает правила проверки.
 */
final class KeyboardException extends ValidationException {}
