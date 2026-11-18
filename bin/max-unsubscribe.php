<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use MaxMessenger\Bot\Dev\MaxUnsubscribe;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

MaxUnsubscribe::main();
