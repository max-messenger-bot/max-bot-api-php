<?php

declare(strict_types=1);

/**
 * Скрипт для генерации operations.csv из схемы API.
 *
 * Usage: php dev/scripts/generate-operations-csv.php
 */

$schemaPath = __DIR__ . '/../../schemas/schema_current.yaml';
$outputPath = __DIR__ . '/../operations.csv';

// Используем PECL yaml модуль
$schema = yaml_parse_file($schemaPath);

if ($schema === false) {
    die("Не удалось прочитать схему: $schemaPath\n");
}

$operations = [];

foreach ($schema['paths'] ?? [] as $path => $pathItem) {
    foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
        if (isset($pathItem[$method])) {
            $operation = $pathItem[$method];
            $operations[] = [
                'operationId' => $operation['operationId'] ?? '',
                'summary' => $operation['summary'] ?? '',
                'description' => $operation['description'] ?? '',
            ];
        }
    }
}

// Сортируем по operationId
usort($operations, static fn($a, $b) => $a['operationId'] <=> $b['operationId']);

// Записываем в CSV
$fp = fopen($outputPath, 'w');
if ($fp === false) {
    die("Не удалось создать файл: $outputPath\n");
}

// Заголовок
fputcsv($fp, ['operationId', 'summary', 'description']);

// Данные
foreach ($operations as $op) {
    fputcsv($fp, $op);
}

fclose($fp);

echo sprintf("Создано %s записей в %s\n", count($operations), $outputPath);
