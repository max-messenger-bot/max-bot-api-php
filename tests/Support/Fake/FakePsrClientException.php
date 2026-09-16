<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Tests\Support\Fake;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

/**
 * Ошибка клиента PSR-18, не связанная с сетью, для тестов.
 */
final class FakePsrClientException extends RuntimeException implements ClientExceptionInterface {}
