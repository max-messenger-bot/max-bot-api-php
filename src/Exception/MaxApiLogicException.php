<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Exception;

use LogicException;

/**
 * Логическая ошибка.
 *
 * Базовый класс логических исключений клиента API МАКС.
 */
abstract class MaxApiLogicException extends LogicException {}
