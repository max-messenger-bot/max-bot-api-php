<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;

/**
 * Консольная команда для удаления Webhook подписки Max Bot API
 *
 * Запрашивает у пользователя:
 * - API key (токен доступа)
 * - Выбор подписки для удаления
 *
 * Выводит список всех подписок, удаляет выбранную и показывает обновлённый список
 */
class MaxUnsubscribe
{

    /**
     * Основная функция
     */
    public static function main(): void
    {
        echo "\n";
        echo sprintf("%s\n", str_repeat('█', 50));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("█%s█\n", mb_str_pad('Удаление Webhook подписки Max Bot API', 48, ' ', STR_PAD_BOTH));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("%s\n", str_repeat('█', 50));

        // Шаг 1: API Key
        $apiKey = self::requestApiKey();
        echo "✓ API key принят\n";

        // Создание API клиента
        $client = new MaxApiClient($apiKey);

        // Получение списка подписок и удаление выбранной
        self::unsubscribe($client);
    }

    /**
     * Очистка ввода от лишних символов
     *
     * @return non-empty-string
     */
    private static function readInput(string $prompt): string
    {
        while (true) {
            echo $prompt;
            $input = fgets(STDIN);

            if ($input === false) {
                exit(1);
            }

            $trimmed = trim($input);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }
    }

    /**
     * Запрос API key
     */
    private static function requestApiKey(): string
    {
        echo sprintf("\n%s\n", str_repeat('═', 50));
        echo "ШАГ 1: API Key (токен доступа)\n";
        echo sprintf("%s\n\n", str_repeat('═', 50));

        echo "Введите ваш Max Bot API token\n";
        echo "Получить токен можно через @MasterBot\n";

        $apiKey = self::readInput("\nAPI Key: ");

        if (strlen($apiKey) < 10) {
            echo "❌ Слишком короткий API key\n";
            return self::requestApiKey();
        }

        // Проверка API ключа
        if (!self::validateApiKey($apiKey)) {
            return self::requestApiKey();
        }

        return $apiKey;
    }

    /**
     * Получение списка подписок, выбор и удаление
     */
    private static function unsubscribe(MaxApiClient $client): void
    {
        echo "\n🔄 Получение списка подписок... ";

        try {
            $result = $client->getSubscriptions();
            $subscriptions = $result->getSubscriptions();

            echo "✅ Получено\n\n";

            if (empty($subscriptions)) {
                echo "ℹ️  Подписки не найдены\n\n";
                return;
            }

            // Вывод текущего списка подписок
            echo sprintf("%s\n", str_repeat('─', 50));
            echo sprintf("Найдено подписок: %d\n", count($subscriptions));
            echo sprintf("%s\n\n", str_repeat('─', 50));

            foreach ($subscriptions as $index => $subscription) {
                $number = $index + 1;
                echo sprintf("%d. %s\n", $number, $subscription->getUrl());

                // Время создания
                $time = $subscription->getTime();
                $timeFormatted = $time->format('Y-m-d H:i:s');
                echo sprintf("   • Дата создания: %s\n", $timeFormatted);

                // Версия API
                $version = $subscription->getVersion();
                if ($version !== null) {
                    echo sprintf("   • Версия API: %s\n", $version);
                }

                // Типы обновлений
                $updateTypes = $subscription->getUpdateTypes();
                if ($updateTypes === null || $updateTypes === []) {
                    echo "   • Типы обновлений: все\n";
                } else {
                    echo "   • Типы обновлений:\n";
                    foreach ($updateTypes as $type) {
                        echo sprintf("     ┃ %s\n", $type->value);
                    }
                }

                echo "\n";
            }

            echo sprintf("%s\n", str_repeat('─', 50));

            // Запрос номера подписки для удаления
            echo "\nВведите номер подписки для удаления (или 0 для отмены): ";
            $input = fgets(STDIN);

            if ($input === false) {
                exit(1);
            }

            $input = trim($input);

            if ($input === '0') {
                echo "\nℹ️  Отмена операции\n\n";
                return;
            }

            // Проверка ввода (только цифры)
            if (!preg_match('/^\d+$/', $input)) {
                echo "❌ Ошибка: введите только число\n\n";
                return;
            }

            $selectedIndex = (int)$input - 1;

            if (!isset($subscriptions[$selectedIndex])) {
                echo "❌ Неверный номер подписки\n\n";
                return;
            }

            $subscriptionToDelete = $subscriptions[$selectedIndex];
            $urlToDelete = $subscriptionToDelete->getUrl();

            // Подтверждение удаления
            echo sprintf("\n⚠️  Вы уверены, что хотите удалить подписку?\n   URL: %s\n", $urlToDelete);
            echo 'Введите "yes" для подтверждения: ';
            $confirm = self::readInput('');

            if (strtolower($confirm) !== 'yes') {
                echo "\nℹ️  Отмена удаления\n\n";
                return;
            }

            // Удаление подписки
            echo "\n🔄 Удаление подписки... ";
            $client->unsubscribe($urlToDelete);
            echo "✅ Успешно\n";

            // Проверка удаления и вывод обновлённого списка
            echo "\n🔄 Получение обновлённого списка подписок... ";
            $result = $client->getSubscriptions();
            $subscriptions = $result->getSubscriptions();
            echo "✅ Получено\n\n";

            if (empty($subscriptions)) {
                echo "ℹ️  Подписки не найдены\n\n";
                echo sprintf("%s\n", str_repeat('─', 50));
                echo "✅ Подписка успешно удалена\n";
                echo sprintf("%s\n\n", str_repeat('─', 50));
                return;
            }

            echo sprintf("%s\n", str_repeat('─', 50));
            echo sprintf("Найдено подписок: %d\n", count($subscriptions));
            echo sprintf("%s\n\n", str_repeat('─', 50));

            foreach ($subscriptions as $index => $subscription) {
                $number = $index + 1;
                echo sprintf("%d. %s\n", $number, $subscription->getUrl());

                // Время создания
                $time = $subscription->getTime();
                $timeFormatted = $time->format('Y-m-d H:i:s');
                echo sprintf("   • Дата создания: %s\n", $timeFormatted);

                // Версия API
                $version = $subscription->getVersion();
                if ($version !== null) {
                    echo sprintf("   • Версия API: %s\n", $version);
                }

                // Типы обновлений
                $updateTypes = $subscription->getUpdateTypes();
                if ($updateTypes === null || $updateTypes === []) {
                    echo "   • Типы обновлений: все\n";
                } else {
                    echo "   • Типы обновлений:\n";
                    foreach ($updateTypes as $type) {
                        echo sprintf("     ┃ %s\n", $type->value);
                    }
                }

                echo "\n";
            }

            echo sprintf("%s\n", str_repeat('─', 50));
            echo "✅ Подписка успешно удалена\n";
            echo sprintf("%s\n\n", str_repeat('─', 50));
        } catch (SimpleQueryError $e) {
            echo sprintf("❌ Ошибка API: %s\n", $e->getMessage());
            exit(1);
        } catch (Throwable $e) {
            echo sprintf("❌ Ошибка: %s\n", $e->getMessage());
            exit(1);
        }
    }

    /**
     * Проверка API ключа через вызов getMyInfo()
     */
    private static function validateApiKey(string $apiKey): bool
    {
        echo "\n🔄 Проверка API key... ";

        try {
            $client = new MaxApiClient($apiKey);
            $client->getMyInfo();
            echo "✅ Успешно\n";
            return true;
        } catch (SimpleQueryError $e) {
            echo sprintf("❌ Ошибка API: %s\n", $e->getMessage());
            return false;
        } catch (Throwable $e) {
            echo sprintf("❌ Ошибка: %s\n", $e->getMessage());
            return false;
        }
    }
}

// Запуск
MaxUnsubscribe::main();
