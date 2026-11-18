<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Dev;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use Throwable;

use function sprintf;

/**
 * Консольная команда для просмотра Webhook подписок Max Bot API
 *
 * Запрашивает у пользователя:
 * - Токен доступа
 *
 * Выводит список всех подписок в виде двухуровневого списка
 *
 * @psalm-cli-only
 */
final class MaxSubscribes
{
    /**
     * Основная функция
     */
    public static function main(): void
    {
        Utils::handleHelp(
            'max-subscribes',
            'Выводит список активных Webhook-подписок с датой создания, версией API и типами событий.',
        );

        Utils::printHeader('Просмотр Webhook подписок Max Bot API');

        // Шаг 1: Токен доступа
        $accessToken = Utils::requestAccessToken();
        echo "✓ Токен доступа принят\n";

        // Создание API клиента
        $client = Utils::createApiClient($accessToken);

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
