<?php

declare(strict_types=1);

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MaxMessenger\Bot\Bin\Utils;
use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Responses\BotInfo;
use MaxMessenger\Bot\Models\Responses\Chat;

/**
 * Консольная команда для просмотра списка чатов и удаления чатов Max Bot API
 *
 * Запрашивает у пользователя:
 * - API key (токен доступа)
 *
 * Выводит список чатов бота с поддержкой пагинации
 */
class MaxDeleteChats
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
     * Удаление чата с подтверждением
     *
     * @param list<Chat> $ownerChats
     */
    private static function deleteChat(array &$ownerChats, int $selectedIndex, MaxApiClient $client): void
    {
        $selectedChat = $ownerChats[$selectedIndex];
        $chatTitle = $selectedChat->getTitle() ?? '(без названия)';

        // Подтверждение удаления
        echo "\n";
        echo sprintf("⚠️  Вы уверены, что хотите безвозвратно удалить чат \"%s\"?\n", $chatTitle);
        echo "   Введите 'yes' для подтверждения: ";
        $confirm = trim(fgets(STDIN));

        if ($confirm !== 'yes') {
            echo "❌ Удаление отменено\n";
            return;
        }

        // Удаление чата
        echo '🔄 Удаление чата... ';
        try {
            $client->deleteChat($selectedChat->getChatId());
            echo "✅ Чат удалён\n";

            // Удаление чата из текущего списка
            unset($ownerChats[$selectedIndex]);
            $ownerChats = array_values($ownerChats);
        } catch (SimpleQueryError $e) {
            echo sprintf("❌ Ошибка API при удалении: %s\n", $e->getMessage());
        } catch (Throwable $e) {
            echo sprintf("❌ Ошибка при удалении: %s\n", $e->getMessage());
        }
    }

    /**
     * Получение и вывод списка чатов с поддержкой пагинации и удалением
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

                echo sprintf("✅ Получено: %s чат(ов)\n", count($chats));

                // Фильтрация: оставляем только чаты, где бот владелец
                $ownerChats = [];
                foreach ($chats as $chat) {
                    if ($chat->getOwnerId() === $botInfo->getUserId()) {
                        $ownerChats[] = $chat;
                    }
                }

                if (empty($ownerChats)) {
                    echo "ℹ️  Чатов, где бот владелец, на странице не найдено\n";
                } else {
                    // Отображение страницы и получение действия от пользователя
                    self::printChatPage($ownerChats, $botInfo, $client);
                }

                if ($marker === null) {
                    break;
                }
                $pageNumber++;
            } catch (SimpleQueryError $e) {
                echo sprintf("❌ Ошибка API: %s\n", $e->getMessage());
                exit(1);
            } catch (Throwable $e) {
                echo sprintf("❌ Ошибка: %s\n", $e->getMessage());
                exit(1);
            }
        } while (true);

        echo "\n";
        echo sprintf("%s\n", str_repeat('═', 50));
        echo "✅ Просмотр чатов завершён\n";
        echo sprintf("%s\n", str_repeat('═', 50));
    }

    /**
     * Отображение страницы чатов и получение действия от пользователя
     * Работает в цикле до тех пор, пока пользователь не решит перейти на следующую страницу
     *
     * @param list<Chat> $ownerChats
     */
    private static function printChatPage(array &$ownerChats, BotInfo $botInfo, MaxApiClient $client): void
    {
        while (true) {
            echo sprintf("📋 Чатов, где бот владелец: %s\n\n", count($ownerChats));

            Utils::printChatList($ownerChats, $botInfo);

            // Предложение удалить чат или перейти к следующей странице
            echo "\n";
            echo 'Введите номер чата для удаления'
                . ' (555 для удаления всех частов на странице или Enter для следующей страницы): ';
            $input = trim(fgets(STDIN));

            if ($input === '') {
                // Пользователь нажал Enter - выход для перехода к следующей странице
                return;
            }

            // Проверка ввода: должно быть число
            if (!preg_match('/^\d+$/', $input)) {
                echo "❌ Ошибка: введите номер чата\n";
                continue;
            }

            if ($input === '555') {
                foreach (array_keys($ownerChats) as $index) {
                    self::deleteChat($ownerChats, $index, $client);
                }
            } else {
                $selectedIndex = (int)$input - 1;

                if (!isset($ownerChats[$selectedIndex])) {
                    echo "❌ Ошибка: неверный номер чата\n";
                    continue;
                }

                // Удаление выбранного чата
                self::deleteChat($ownerChats, $selectedIndex, $client);
            }

            // Если страница стала пустой после удаления, выходим
            if (empty($ownerChats)) {
                echo "ℹ️  Страница пуста после удаления\n";
                return;
            }
        }
    }
}

// Запуск
MaxDeleteChats::main();
