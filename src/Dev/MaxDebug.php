<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Dev;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Response\Update;
use Throwable;

use function count;
use function date;
use function json_encode;
use function sleep;
use function sprintf;

/**
 * Консольная команда для отладки Max Bot API через Long Polling
 *
 * Запрашивает у пользователя:
 * - Токен доступа
 *
 * Если есть подписки - выводит сообщение о невозможности Long Polling
 * Если подписок нет - запускает бесконечный цикл получения событий
 */
final class MaxDebug
{
    /**
     * Основная функция
     */
    public static function main(): void
    {
        Utils::printHeader('Отладка Max Bot API (Long Polling)');

        // Шаг 1: Токен доступа
        $accessToken = Utils::requestAccessToken();
        echo "✓ Токен доступа принят\n";

        // Создание API клиента
        $client = new MaxApiClient($accessToken);

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
                Utils::printLine();
                echo "⚠️  Получение событий методом \"Long Polling\" невозможно при наличии подписок.\n";
                echo "\n";
                echo sprintf("    Найдено активных подписок: %d\n", count($subscriptions));
                echo "\n";
                echo "    Для использования Long Polling удалите все подписки:\n";
                echo "    - Используйте скрипт max-unsubscribe.php\n";
                Utils::printLine(true);

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
     * Подробный вывод информации о событии
     */
    private static function printUpdate(Update $update, int $number): void
    {
        Utils::printLine();
        echo sprintf("Событие #%d\n", $number);
        Utils::printLine();

        // Тип события
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

        Utils::printLine(true);
    }

    /**
     * Бесконечный цикл Long Polling
     */
    private static function runLongPolling(MaxApiClient $client): void
    {
        Utils::printLine();
        echo "🚀 Запуск Long Polling...\n";
        echo "Нажмите Ctrl+C для остановки\n";
        Utils::printLine(true);

        $marker = null;
        $updateCount = 0;

        while (true) {
            try {
                echo sprintf("[%s] Ожидание событий...\n", date('Y-m-d H:i:s'));

                $response = $client->getUpdates(
                    timeout: 60,
                    marker: $marker,
                );

                $updates = $response->getUpdates();
                $marker = $response->getMarker();

                if (empty($updates)) {
                    echo sprintf("[%s] Событий нет\n\n", date('Y-m-d H:i:s'));
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
