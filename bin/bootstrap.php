<?php

declare(strict_types=1);

if (PHP_VERSION_ID < 80200) {
    fwrite(STDERR, sprintf("Для запуска требуется PHP версии не ниже 8.2. Текущая версия: %s\n", PHP_VERSION));
    exit(1);
}

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Скрипт предназначен только для запуска из командной строки.\n");
    exit(1);
}

$autoload = null;
$candidates = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 3) . '/autoload.php',
];
foreach ($candidates as $candidate) {
    if (file_exists($candidate)) {
        $autoload = $candidate;
        break;
    }
}

if ($autoload === null) {
    fwrite(STDERR, "Зависимости Composer не установлены. Для установки выполните:\n\n    composer install\n\n");
    exit(1);
}

/** @psalm-suppress UnresolvableInclude */
require_once $autoload;
