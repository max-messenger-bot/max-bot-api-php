<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Dev;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use Throwable;

use function sprintf;

/**
 * Консольная команда для удаления Webhook подписки Max Bot API
 *
 * Запрашивает у пользователя:
 * - Токен доступа
 * - Выбор подписки для удаления
 *
 * Выводит список всех подписок, удаляет выбранную и показывает обновлённый список
 */
final class MaxUnsubscribe
{
    /**
     * Основная функция
     */
    public static function main(): void
    {
        Utils::printHeader('Удаление Webhook подписки Max Bot API');

        // Шаг 1: Токен доступа
        $accessToken = Utils::requestAccessToken();
        echo "✓ Токен доступа принят\n";

        // Создание API клиента
        $client = new MaxApiClient($accessToken);

        // Получение списка подписок и удаление выбранной
        self::unsubscribe($client);
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

            Utils::printSubscriptionList($subscriptions);

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

            $selectedIndex = (int) $input - 1;

            if (!isset($subscriptions[$selectedIndex])) {
                echo "❌ Неверный номер подписки\n\n";

                return;
            }

            $subscriptionToDelete = $subscriptions[$selectedIndex];
            $urlToDelete = $subscriptionToDelete->getUrl();

            // Подтверждение удаления
            echo sprintf("\n⚠️  Вы уверены, что хотите удалить подписку?\n   URL: %s\n", $urlToDelete);
            echo 'Введите "yes" для подтверждения: ';
            $confirm = Utils::readInput('');

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
                Utils::printLine();
                echo "✅ Подписка успешно удалена\n";
                Utils::printLine(true);

                return;
            }

            Utils::printSubscriptionList($subscriptions);

            echo "✅ Подписка успешно удалена\n";
            Utils::printLine(true);
        } catch (SimpleQueryError $e) {
            echo sprintf("❌ Ошибка API: %s\n", $e->getMessage());

            exit(1);
        } catch (Throwable $e) {
            echo sprintf("❌ Ошибка: %s\n", $e->getMessage());

            exit(1);
        }
    }
}
