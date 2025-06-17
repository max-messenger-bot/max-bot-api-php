<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Bin;

use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Responses\BotInfo;
use MaxMessenger\Bot\Models\Responses\Chat;
use MaxMessenger\Bot\Models\Responses\Subscription;
use Throwable;

use function count;
use function printf;
use function sprintf;
use function strlen;

/**
 * Утилиты для консольных скриптов
 */
class Utils
{
    /**
     * Путь к файлу с ключами
     */
    private const KEYS_FILE = __DIR__ . '/.keys';

    /**
     * Получение API ключа из файла .keys
     *
     * Если файл существует и содержит ключи, предлагает выбрать из списка.
     * Если файл не существует, выводит рекомендацию создать его.
     *
     * @return non-empty-string|null Возвращает выбранный ключ или null, если пользователь отменил выбор
     */
    public static function getApiKeyFromKeysFile(): ?string
    {
        if (!file_exists(self::KEYS_FILE)) {
            echo "\n";
            echo "ℹ️  Файл .keys не найден\n";
            echo "   Чтобы не вводить ключ каждый раз, создайте файл .keys\n";
            echo "   в папке bin/ и добавьте в него ключи в формате:\n";
            echo "   key-name=api-key\n";
            echo "\n";
            return null;
        }

        $keys = self::loadKeysFromFile();

        if (empty($keys)) {
            echo "\n";
            echo "⚠️  Файл .keys пуст или содержит некорректные данные\n";
            echo "   Добавьте ключи в формате: key-name=api-key\n";
            echo "\n";
            return null;
        }

        return self::selectKeyFromList($keys);
    }

    /**
     * Вывод списка чатов
     *
     * @param list<Chat> $chats
     */
    public static function printChatList(array $chats, BotInfo $botInfo): void
    {
        if (empty($chats)) {
            echo "ℹ️  Чаты не найдены\n\n";
            return;
        }

        echo sprintf("%s\n", str_repeat('─', 50));
        echo sprintf("Чатов на странице: %d\n", count($chats));
        echo sprintf("%s\n\n", str_repeat('─', 50));

        foreach ($chats as $index => $chat) {
            $number = $index + 1;

            // Заголовок чата
            $title = $chat->getTitle();
            $admin = $chat->getOwnerId() === $botInfo->getUserId() ? ' (Owner)' : '';
            if ($title !== null) {
                echo sprintf("%d. %s (%d)%s\n", $number, $title, $chat->getChatId(), $admin);
            } else {
                echo sprintf("%d. (%d)%s\n", $number, $chat->getChatId(), $admin);
            }

            // Тип чата
            $chatType = $chat->getType()?->value ?? $chat->getTypeRaw() ?: 'unknown';
            echo sprintf('   • Тип: %s', $chatType);

            // Статус чата
            $status = $chat->getStatus()?->value ?? $chat->getStatusRaw() ?: 'unknown';
            echo sprintf(', статус: %s', $status);

            // Время последнего события
            $lastEventTime = $chat->getLastEventTime();
            $lastEventTimeFormatted = $lastEventTime->format('Y-m-d H:i:s');
            echo sprintf(", Последнее событие: %s\n", $lastEventTimeFormatted);

            // Количество участников
            echo sprintf('   • Участников: %d', $chat->getParticipantsCount());

            // Публичный ли чат
            $public = $chat->isPublic() ? 'публичный' : 'приватный';
            echo sprintf(" (%s)\n", $public);

            // Описание чата
            $description = $chat->getDescription();
            if ($description !== null) {
                echo sprintf("   • Описание: %s\n", $description);
            }

            // Ссылка на чат
            $link = $chat->getLink();
            if ($link !== null) {
                echo sprintf("   • Ссылка: %s\n", $link);
            }

            echo "\n";
        }

        echo sprintf("%s\n", str_repeat('─', 50));
    }

    /**
     * @param list<Subscription> $subscriptions
     */
    public static function printSubscriptionList(array $subscriptions): void
    {
        if (empty($subscriptions)) {
            echo "ℹ️  Подписки не найдены\n\n";
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
    }

    /**
     * Очистка ввода от лишних символов
     *
     * @return non-empty-string
     */
    public static function readInput(string $prompt): string
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
     * Запрос API key у пользователя
     *
     * Сначала пытается получить ключ из файла .keys,
     * если не удалось - запрашивает вручную с проверкой.
     *
     * @return non-empty-string
     */
    public static function requestApiKey(): string
    {
        echo sprintf("\n%s\n", str_repeat('═', 50));
        echo "ШАГ 1: API Key (токен доступа)\n";
        echo sprintf("%s\n\n", str_repeat('═', 50));

        // Попытка получить ключ из файла .keys
        $apiKey = self::getApiKeyFromKeysFile();

        if ($apiKey !== null) {
            // Проверка API ключа
            if (self::validateApiKey($apiKey)) {
                return $apiKey;
            }
            echo "⚠️  Ключ не прошёл проверку, введите другой\n";
        }

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
     * Проверка API ключа через вызов getMyInfo()
     *
     * @param non-empty-string $apiKey API ключ для проверки
     * @return bool true если ключ валиден
     */
    public static function validateApiKey(string $apiKey): bool
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

    /**
     * Загрузка ключей из файла
     *
     * @return array<non-empty-string, non-empty-string> Ассоциативный массив [имя => ключ]
     */
    private static function loadKeysFromFile(): array
    {
        $content = file_get_contents(self::KEYS_FILE);

        if ($content === false) {
            return [];
        }

        $keys = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            // Пропуск пустых строк и комментариев
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);

            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $key = trim($parts[1]);

            if ($name !== '' && $key !== '') {
                $keys[$name] = $key;
            }
        }

        return $keys;
    }

    /**
     * Выбор ключа из списка
     *
     * @param array<non-empty-string, non-empty-string> $keys Ассоциативный массив [имя => ключ]
     * @return non-empty-string|null Выбранный ключ или null, если пользователь хочет ввести свой
     */
    private static function selectKeyFromList(array $keys): ?string
    {
        echo "\n";
        echo "Доступные ключи:\n";
        echo str_repeat('─', 50) . "\n";

        $names = array_keys($keys);
        foreach ($names as $index => $name) {
            printf("  %d. %s\n", $index + 1, $name);
        }

        echo str_repeat('─', 50) . "\n";
        echo 'Введите номер ключа для выбора (или 0 для ввода своего ключа): ';

        $input = fgets(STDIN);

        if ($input === false) {
            return null;
        }

        $input = trim($input);

        // Если ввели 0 - ввод своего ключа
        if ($input === '0') {
            return null;
        }

        // Проверка: ввод должен быть числом
        if (!preg_match('/^\d+$/', $input)) {
            echo "❌ Ошибка: введите номер ключа\n";
            return null;
        }

        $selectedIndex = (int)$input - 1;

        if (!isset($names[$selectedIndex])) {
            echo "❌ Неверный номер ключа\n";
            return null;
        }

        $selectedName = $names[$selectedIndex];
        $selectedKey = $keys[$selectedName];

        echo '✓ Выбран ключ: ' . $selectedName . "\n";

        return $selectedKey;
    }
}
