<?php

declare(strict_types=1);

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MaxMessenger\Bot\Bin\Utils;
use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Responses\Update;

/**
 * Консольная команда для отладки Max Bot API через Long Polling
 *
 * Запрашивает у пользователя:
 * - API key (токен доступа)
 *
 * Если есть подписки - выводит сообщение о невозможности Long Polling
 * Если подписок нет - запускает бесконечный цикл получения обновлений
 */
class MaxDebug
{
    /**
     * Основная функция
     */
    public static function main(): void
    {
        echo "\n";
        echo sprintf("%s\n", str_repeat('█', 50));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("█%s█\n", mb_str_pad('Отладка Max Bot API (Long Polling)', 48, ' ', STR_PAD_BOTH));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("%s\n", str_repeat('█', 50));

        // Шаг 1: API Key
        $apiKey = Utils::requestApiKey();
        echo "✓ API key принят\n";

        // Создание API клиента
        $client = new MaxApiClient($apiKey);

        // Проверка подписок и запуск Long Polling
        self::checkAndRunLongPolling($client);
    }

    /**
     * Проверка подписок и запуск Long Polling
     */
    private static function checkAndRunLongPolling(MaxApiClient $client): void
    {
        echo "\n🔄 Проверка наличия подписок... ";

        try {
            $result = $client->getSubscriptions();
            $subscriptions = $result->getSubscriptions();

            echo "✅ Получено\n\n";

            if (!empty($subscriptions)) {
                echo sprintf("%s\n", str_repeat('─', 50));
                echo "⚠️  Получение обновлений методом \"Long Polling\" невозможно\n";
                echo "    при наличии подписок.\n";
                echo "\n";
                echo sprintf("    Найдено активных подписок: %d\n", count($subscriptions));
                echo "\n";
                echo "    Для использования Long Polling удалите все подписки:\n";
                echo "    - Используйте скрипт max-unsubscribe.php\n";
                echo "    - Или через @MasterBot\n";
                echo sprintf("%s\n\n", str_repeat('─', 50));
                return;
            }

            echo "ℹ️  Подписки не найдены\n";
            echo "✅ Long Polling доступен\n\n";

            // Запуск бесконечного цикла Long Polling
            self::runLongPolling($client);
        } catch (SimpleQueryError $e) {
            echo sprintf("❌ Ошибка API: %s\n", $e->getMessage());
            exit(1);
        } catch (Throwable $e) {
            echo sprintf("❌ Ошибка: %s\n", $e->getMessage());
            exit(1);
        }
    }

    /**
     * Подробный вывод информации об обновлении
     */
    private static function printUpdate(Update $update, int $number): void
    {
        echo sprintf("%s\n", str_repeat('─', 50));
        echo sprintf("Обновление #%d\n", $number);
        echo sprintf("%s\n", str_repeat('─', 50));

        // Тип обновления
        $updateType = $update->getUpdateType();
        $updateTypeRaw = $update->getUpdateTypeRaw();
        echo sprintf("Тип: %s (%s)\n", $updateType?->value ?? 'unknown', $updateTypeRaw);

        // Время
        $timestamp = $update->getTimestamp();
        echo sprintf("Время: %s\n", $timestamp->format('Y-m-d H:i:s'));

        // Raw данные для полной информации
        echo "\nДетали:\n";
        $json = json_encode($update->getRawData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json !== false) {
            echo sprintf("%s\n", $json);
        }

        echo sprintf("%s\n\n", str_repeat('─', 50));
    }

    /**
     * Бесконечный цикл Long Polling
     */
    private static function runLongPolling(MaxApiClient $client): void
    {
        echo sprintf("%s\n", str_repeat('─', 50));
        echo "🚀 Запуск Long Polling...\n";
        echo "Нажмите Ctrl+C для остановки\n";
        echo sprintf("%s\n\n", str_repeat('─', 50));

        $marker = null;
        $updateCount = 0;

        while (true) {
            try {
                echo sprintf("[%s] Ожидание обновлений...\n", date('Y-m-d H:i:s'));

                $response = $client->getUpdates(
                    timeout: 60,
                    marker: $marker
                );

                $updates = $response->getUpdates();
                $marker = $response->getMarker();

                if (empty($updates)) {
                    echo sprintf("[%s] Обновлений нет\n\n", date('Y-m-d H:i:s'));
                } else {
                    foreach ($updates as $update) {
                        $updateCount++;
                        self::printUpdate($update, $updateCount);
                    }
                    echo "\n";
                }
            } catch (SimpleQueryError $e) {
                echo sprintf("❌ Ошибка API: %s\n\n", $e->getMessage());
                sleep(5);
            } catch (Throwable $e) {
                echo sprintf("❌ Ошибка: %s\n\n", $e->getMessage());
                sleep(5);
            }
        }
    }
}

// Запуск
MaxDebug::main();
