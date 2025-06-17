<?php

declare(strict_types=1);

$json = file_get_contents(__DIR__ . '/swagger.json');

if (!is_string($json)) {
    exit('File "swagger.json" not found.' . PHP_EOL);
}

try {
    $data = json_decode($json, false, 16, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    exit('Decode error: ' . $e->getMessage() . PHP_EOL);
}

try {
    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (JsonException $e) {
    exit('Encode error: ' . $e->getMessage() . PHP_EOL);
}

if (file_put_contents(__DIR__ . '/swagger-formatted.json', $json, LOCK_EX) !== strlen($json)) {
    exit('Failed to save data to file "swagger-formatted.json".');
}

exit('OK' . PHP_EOL);
