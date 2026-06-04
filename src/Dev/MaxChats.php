<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Dev;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Response\BotInfo;
use Throwable;

use function count;
use function sprintf;

/**
 * Консольная команда для просмотра списка чатов Max Bot API
 *
 * Запрашивает у пользователя:
 * - Токен доступа
 *
 * Выводит список чатов бота с поддержкой пагинации
 */
final class MaxChats
{
    /**
     * Основная функция
     */
    public static function main(): void
    {
        Utils::printHeader('Просмотр списка чатов Max Bot API');

        // Шаг 1: Токен доступа
        $accessToken = Utils::requestAccessToken();
        echo "✓ Токен доступа принят\n";

        // Создание API клиента
        $client = new MaxApiClient($accessToken);

        $botInfo = $client->getMyInfo();

        // Получение списка чатов
        self::listChats($client, $botInfo);
    }

    /**
     * Получение и вывод списка чатов с поддержкой пагинации
     */
    private static function listChats(MaxApiClient $client, BotInfo $botInfo): void
    {
        $marker = null;
        $pageNumber = 1;

        do {
            echo "\n";
            echo sprintf("═ Страница %d ═\n\n", $pageNumber);
            echo '🔄 Получение списка чатов... ';

            try {
                $result = $client->getChats(5, $marker);
                $chats = $result->getChats();
                $marker = $result->getMarker();

                echo sprintf("✅ Получено: %s чат(ов)\n\n", count($chats));

                Utils::printChatList($chats, $botInfo);

                if ($marker !== null) {
                    echo "\n";
                    echo "📄 Доступна следующая страница\n";
                    echo '   Нажмите Enter для продолжения (или Ctrl+C для выхода)... ';

                    $input = fgets(STDIN);
                    if ($input === false) {
                        break;
                    }

                    $pageNumber++;
                }
            } catch (SimpleQueryError $e) {
                echo sprintf("❌ Ошибка API: %s\n", $e->getMessage());

                exit(1);
            } catch (Throwable $e) {
                echo sprintf("❌ Ошибка: %s\n", $e->getMessage());

                exit(1);
            }
        } while ($marker !== null);

        Utils::printDoubleLine(false, true);
        echo "✅ Просмотр чатов завершён\n";
        Utils::printDoubleLine();
    }
}
