<?php

declare(strict_types=1);

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MaxMessenger\Bot\Bin\Utils;
use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Responses\BotInfo;

/**
 * Консольная команда для просмотра списка чатов Max Bot API
 *
 * Запрашивает у пользователя:
 * - API key (токен доступа)
 *
 * Выводит список чатов бота с поддержкой пагинации
 */
class MaxChats
{
    /**
     * Основная функция
     */
    public static function main(): void
    {
        echo "\n";
        echo sprintf("%s\n", str_repeat('█', 50));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("█%s█\n", mb_str_pad('Просмотр списка чатов Max Bot API', 48, ' ', STR_PAD_BOTH));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("%s\n", str_repeat('█', 50));

        // Шаг 1: API Key
        $apiKey = Utils::requestApiKey();
        echo "✓ API key принят\n";

        // Создание API клиента
        $client = new MaxApiClient($apiKey);

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

        echo "\n";
        echo sprintf("%s\n", str_repeat('═', 50));
        echo "✅ Просмотр чатов завершён\n";
        echo sprintf("%s\n", str_repeat('═', 50));
    }
}

// Запуск
MaxChats::main();
