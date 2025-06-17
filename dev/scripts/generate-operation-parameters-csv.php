<?php

declare(strict_types=1);

/**
 * Скрипт для генерации operation-parameters.csv из схемы API.
 *
 * Генерирует таблицу параметров для каждой операции API.
 *
 * Columns:
 * - operationId: ID операции
 * - parameterName: Имя параметра
 * - in: Расположение параметра (path, query, requestBody) с указанием required
 * - type: Тип ожидаемых данных
 * - constraints: Ограничения (minimum, maximum, minLength, maxLength, minItems, maxItems, pattern и др.)
 * - description: Описание параметра
 *
 * Usage: php dev/scripts/generate-operation-parameters-csv.php
 */

$schemaPath = __DIR__ . '/../../schemas/schema_current.yaml';
$outputPath = __DIR__ . '/../operation-parameters.csv';

// Используем PECL yaml модуль
/**
 * @var array{
 *     paths: array<string, array<string, array{
 *         operationId?: string,
 *         parameters?: array<int, array{
 *             name?: string,
 *             in?: string,
 *             required?: bool,
 *             schema?: array{
 *                 $ref?: string,
 *                 type?: string,
 *                 format?: string,
 *                 items?: mixed,
 *                 minimum?: int|float,
 *                 maximum?: int|float,
 *                 minLength?: int,
 *                 maxLength?: int,
 *                 pattern?: string,
 *                 minItems?: int,
 *                 maxItems?: int,
 *                 uniqueItems?: bool,
 *                 enum?: array<int, mixed>,
 *                 default?: mixed,
 *                 ...<string, mixed>
 *             },
 *             description?: string
 *         }>,
 *         requestBody?: array{
 *             required?: bool,
 *             description?: string,
 *             content?: array<string, array{
 *                 schema?: array{
 *                     $ref?: string,
 *                     type?: string,
 *                     format?: string,
 *                     items?: mixed,
 *                     ...<string, mixed>
 *                 }
 *             }>
 *         }
 *     }>>,
 *     components: array{
 *         schemas: array<string, array{
 *             description?: string
 *         }>
 *     }
 * }|false $schema
 */
$schema = yaml_parse_file($schemaPath);

if ($schema === false) {
    die("Не удалось прочитать схему: $schemaPath\n");
}

$parametersList = [];

foreach ($schema['paths'] ?? [] as $pathItem) {
    foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
        if (!isset($pathItem[$method])) {
            continue;
        }

        $operation = $pathItem[$method];
        $operationId = $operation['operationId'] ?? '';

        if ($operationId === '') {
            continue;
        }

        // Параметры из parameters (path, query)
        foreach ($operation['parameters'] ?? [] as $param) {
            $name = $param['name'] ?? '';
            $in = $param['in'] ?? '';
            $required = isset($param['required']) && $param['required'] ? 'required' : '';
            $inStr = $in;
            if ($required !== '') {
                $inStr .= " ($required)";
            }

            $paramSchema = $param['schema'] ?? [];
            $type = getTypeString($paramSchema);
            $constraints = getConstraints($paramSchema);

            $parametersList[] = [
                'operationId' => $operationId,
                'parameterName' => $name,
                'in' => $inStr,
                'type' => $type,
                'constraints' => $constraints,
                'description' => $param['description'] ?? '',
            ];
        }

        // requestBody
        if (isset($operation['requestBody'])) {
            $requestBody = $operation['requestBody'];
            $required = isset($requestBody['required']) && $requestBody['required'] ? 'required' : '';
            $inStr = 'requestBody';
            if ($required !== '') {
                $inStr .= " ($required)";
            }

            $content = $requestBody['content'] ?? [];
            $schemaData = $content['application/json']['schema'] ?? [];

            $type = '';
            $constraints = '';
            $description = $requestBody['description'] ?? '';

            // Если схема ссылается на компонент
            if (isset($schemaData['$ref'])) {
                $ref = $schemaData['$ref'];
                $refName = extractRefName($ref);
                $type = $refName;
                // Получаем описание из схемы компонента, если есть
                $componentSchema = getComponentSchema($schema, $refName);
                if (isset($componentSchema['description'])) {
                    $description = getFirstLine($componentSchema['description']);
                }
            } elseif (isset($schemaData['type'])) {
                $type = getTypeString($schemaData);
                $constraints = getConstraints($schemaData);
            }

            $parametersList[] = [
                'operationId' => $operationId,
                'parameterName' => 'requestBody',
                'in' => $inStr,
                'type' => $type,
                'constraints' => $constraints,
                'description' => $description,
            ];
        }
    }
}

// Сортируем по operationId
/**
 * @psalm-suppress MixedArrayAccess
 */
usort($parametersList, static fn($a, $b) => $a['operationId'] <=> $b['operationId']);

// Записываем в CSV
$fp = fopen($outputPath, 'w');
if ($fp === false) {
    die("Не удалось создать файл: $outputPath\n");
}

// Заголовок
fputcsv($fp, ['operationId', 'parameterName', 'in', 'type', 'constraints', 'description']);

// Данные
foreach ($parametersList as $param) {
    fputcsv($fp, $param);
}

fclose($fp);

echo sprintf("Создано %s записей в %s\n", count($parametersList), $outputPath);

/**
 * Извлекает имя компонента из $ref.
 */
function extractRefName(string $ref): string
{
    // #/components/schemas/BotInfo -> BotInfo
    if (preg_match('#/components/schemas/([^/]+)$#', $ref, $matches)) {
        return $matches[1];
    }
    return $ref;
}

/**
 * Возвращает строку типа из схемы.
 *
 * @param array{$ref?: string, type?: string, format?: string, items?: mixed, ...<string, mixed>} $schema
 */
function getTypeString(array $schema): string
{
    if (isset($schema['$ref'])) {
        return extractRefName($schema['$ref']);
    }

    $type = $schema['type'] ?? 'mixed';
    $format = $schema['format'] ?? '';

    if ($format !== '') {
        return "$type ($format)";
    }

    // Если array с items
    if ($type === 'array' && isset($schema['items'])) {
        /** @var array<string, mixed> $items */
        $items = $schema['items'];
        $itemsType = getTypeString($items);
        return "array<$itemsType>";
    }

    return $type;
}

/**
 * Возвращает строку ограничений из схемы.
 *
 * @param array{
 *     minimum?: int|float,
 *     maximum?: int|float,
 *     exclusiveMinimum?: int|float,
 *     exclusiveMaximum?: int|float,
 *     minLength?: int,
 *     maxLength?: int,
 *     pattern?: string,
 *     minItems?: int,
 *     maxItems?: int,
 *     uniqueItems?: bool,
 *     enum?: array<int, mixed>,
 *     default?: mixed, ...<string, mixed>
 * } $schema
 */
function getConstraints(array $schema): string
{
    $constraints = [];

    // Числовые ограничения
    if (isset($schema['minimum'])) {
        $constraints[] = "minimum: {$schema['minimum']}";
    }
    if (isset($schema['maximum'])) {
        $constraints[] = "maximum: {$schema['maximum']}";
    }
    if (isset($schema['exclusiveMinimum'])) {
        $constraints[] = "exclusiveMinimum: {$schema['exclusiveMinimum']}";
    }
    if (isset($schema['exclusiveMaximum'])) {
        $constraints[] = "exclusiveMaximum: {$schema['exclusiveMaximum']}";
    }

    // Строковые ограничения
    if (isset($schema['minLength'])) {
        $constraints[] = "minLength: {$schema['minLength']}";
    }
    if (isset($schema['maxLength'])) {
        $constraints[] = "maxLength: {$schema['maxLength']}";
    }
    if (isset($schema['pattern'])) {
        $constraints[] = 'pattern: ' . var_export($schema['pattern'], true);
    }

    // Массивы
    if (isset($schema['minItems'])) {
        $constraints[] = "minItems: {$schema['minItems']}";
    }
    if (isset($schema['maxItems'])) {
        $constraints[] = "maxItems: {$schema['maxItems']}";
    }
    if (isset($schema['uniqueItems'])) {
        $constraints[] = 'uniqueItems: ' . ($schema['uniqueItems'] === true ? 'true' : 'false');
    }

    // Enum
    if (isset($schema['enum'])) {
        /** @var array<int, mixed> $enumValues */
        $enumValues = $schema['enum'];
        $enumStrings = array_map(static fn($v) => is_bool($v) ? ($v ? 'true' : 'false') : (string)$v, $enumValues);
        $constraints[] = 'enum: [' . implode(', ', $enumStrings) . ']';
    }

    // Default
    if (isset($schema['default'])) {
        /** @psalm-var mixed $default */
        $default = $schema['default'];
        if (is_bool($default)) {
            $constraints[] = 'default: ' . ($default ? 'true' : 'false');
        } else {
            $constraints[] = "default: $default";
        }
    }

    return implode('; ', $constraints);
}

/**
 * Получает схему компонента по имени.
 *
 * @param array{
 *     components: array{
 *         schemas: array<string, array{
 *             description?: string,
 *             ...<string, mixed>
 *         }>
 *     },
 *     ...<string, mixed>
 * } $schema
 * @return array{description?: string, ...<string, mixed>}
 */
function getComponentSchema(array $schema, string $componentName): array
{
    return $schema['components']['schemas'][$componentName] ?? [];
}

/**
 * Возвращает первую строку описания.
 */
function getFirstLine(string $description): string
{
    $lines = explode("\n", $description);
    return trim($lines[0]);
}
