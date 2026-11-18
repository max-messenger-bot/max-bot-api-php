<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use MaxMessenger\Bot\Dev\MaxDebug;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

MaxDebug::main();
