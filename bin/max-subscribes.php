<?php

declare(strict_types=1);

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MaxMessenger\Bot\Bin\Utils;
use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;

/**
 * Консольная команда для просмотра Webhook подписок Max Bot API
 *
 * Запрашивает у пользователя:
 * - API key (токен доступа)
 *
 * Выводит список всех подписок в виде двухуровневого списка
 */
class MaxSubscribes
{
    /**
     * Основная функция
     */
    public static function main(): void
    {
        echo "\n";
        echo sprintf("%s\n", str_repeat('█', 50));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("█%s█\n", mb_str_pad('Просмотр Webhook подписок Max Bot API', 48, ' ', STR_PAD_BOTH));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("%s\n", str_repeat('█', 50));

        // Шаг 1: API Key
        $apiKey = Utils::requestApiKey();
        echo "✓ API key принят\n";

        // Создание API клиента
        $client = new MaxApiClient($apiKey);

        // Получение списка подписок
        self::listSubscriptions($client);
    }

    /**
     * Получение и вывод списка подписок
     */
    private static function listSubscriptions(MaxApiClient $client): void
    {
        echo "\n🔄 Получение списка подписок... ";

        try {
            $result = $client->getSubscriptions();
            $subscriptions = $result->getSubscriptions();

            echo "✅ Получено\n\n";

            Utils::printSubscriptionList($subscriptions);
        } catch (SimpleQueryError $e) {
            echo sprintf("❌ Ошибка API: %s\n", $e->getMessage());
            exit(1);
        } catch (Throwable $e) {
            echo sprintf("❌ Ошибка: %s\n", $e->getMessage());
            exit(1);
        }
    }
}

// Запуск
MaxSubscribes::main();
