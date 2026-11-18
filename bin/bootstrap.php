<?php

declare(strict_types=1);

if (isset($GLOBALS['_composer_autoload_path']) && is_string($GLOBALS['_composer_autoload_path'])) {
    $autoload = $GLOBALS['_composer_autoload_path'];
} else {
    $isAbsolute = static fn(string $path): bool
        => str_starts_with($path, DIRECTORY_SEPARATOR)
        || (DIRECTORY_SEPARATOR === '\\' && isset($path[1]) && $path[1] === ':');

    $autoload = dirname(__DIR__, 3) . '/autoload.php';
    if (!is_file($autoload)) {
        $root = dirname(__DIR__);
        /** @var array{config?: array{vendor-dir?: string}}|null $composerConfig */
        $composerConfig = json_decode((string) @file_get_contents($root . '/composer.json'), true);
        $vendorDir = $composerConfig['config']['vendor-dir'] ?? 'vendor';
        $vendorPath = $isAbsolute($vendorDir) ? $vendorDir : $root . '/' . $vendorDir;
        $autoload = rtrim($vendorPath, '/\\') . '/autoload.php';
    }
}

if (!is_file($autoload)) {
    fwrite(STDERR, "Зависимости Composer не установлены. Для установки выполните:\n\n    composer install\n\n");
    exit(1);
}

require_once $autoload;
