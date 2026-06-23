<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Dev;

use MaxMessenger\Bot\Exception\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Response\BotInfo;
use MaxMessenger\Bot\Model\Response\Chat;
use MaxMessenger\Bot\Model\Response\Subscription;
use Throwable;

use function array_keys;
use function count;
use function dirname;
use function explode;
use function fgets;
use function file_get_contents;
use function mb_strlen;
use function preg_match;
use function printf;
use function sprintf;
use function str_ends_with;
use function str_pad;
use function str_repeat;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * Утилиты для консольных скриптов
 */
final class Utils
{
    /**
     * Имя файла с токенами доступа
     */
    private const TOKENS_NAME = '.tokens';

    /**
     * Получение токена доступа из файла .tokens
     *
     * Ищет файл в корне проекта и его папке dev/. Если файл найден и содержит
     * токены, предлагает выбрать из списка. Иначе выводит подсказку, как его создать.
     *
     * @return non-empty-string|null Выбранный токен доступа или null, если файл не найден либо выбор отменён
     */
    public static function getAccessTokenFromTokensFile(): ?string
    {
        $candidates = self::projectFileCandidates(self::TOKENS_NAME);
        [$path, $tokens] = self::loadConfigFromFiles($candidates);

        if ($path === null) {
            self::printTokensFileNotFound($candidates);

            return null;
        }

        if (empty($tokens)) {
            echo "\n";
            echo "⚠️  Файл .tokens пуст или содержит некорректные данные\n";
            echo "   Добавьте токены в формате: token-name=access-token\n";
            echo "\n";

            return null;
        }

        return self::selectTokenFromList($tokens);
    }

    /**
     * Загрузка конфигурации из первого существующего файла-кандидата
     *
     * Перебирает пути по порядку и возвращает конфиг из первого прочитанного файла.
     * Подавляем предупреждение через @, так как file_exists() не даёт гарантии
     * наличия файла из-за файлового кеша PHP — единственная надёжная проверка
     * это сам результат чтения.
     *
     * @param list<string> $fileNames Пути-кандидаты в порядке приоритета
     * @return array{0: string|null, 1: array<non-empty-string, non-empty-string>} [найденный путь либо null, конфиг]
     */
    public static function loadConfigFromFiles(array $fileNames): array
    {
        foreach ($fileNames as $fileName) {
            $content = @file_get_contents($fileName);

            if ($content !== false) {
                return [$fileName, self::parseConfig($content)];
            }
        }

        return [null, []];
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

        self::printLine();
        echo sprintf("Чатов на странице: %d\n", count($chats));
        self::printLine(true);

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

        self::printLine();
    }

    public static function printDoubleLine(bool $addEmptyLineAfter = false, bool $addEmptyLineBefore = false): void
    {
        if ($addEmptyLineBefore) {
            echo "\n";
        }
        echo sprintf("%s\n", str_repeat('═', 50));
        if ($addEmptyLineAfter) {
            echo "\n";
        }
    }

    /**
     * @param non-empty-string $title
     */
    public static function printHeader(string $title): void
    {
        echo "\n";
        echo sprintf("%s\n", str_repeat('█', 50));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("█%s█\n", str_pad($title, 48 + strlen($title) - mb_strlen($title), ' ', STR_PAD_BOTH));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("%s\n", str_repeat('█', 50));
    }

    public static function printLine(bool $addEmptyLineAfter = false, bool $addEmptyLineBefore = false): void
    {
        if ($addEmptyLineBefore) {
            echo "\n";
        }
        echo sprintf("%s\n", str_repeat('─', 50));
        if ($addEmptyLineAfter) {
            echo "\n";
        }
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

        self::printLine();
        echo sprintf("Найдено подписок: %d\n", count($subscriptions));
        self::printLine(true);

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

            // Типы событий
            $updateTypes = $subscription->getUpdateTypes();
            if ($updateTypes === null || $updateTypes === []) {
                echo "   • Типы событий: все\n";
            } else {
                echo "   • Типы событий:\n";
                foreach ($updateTypes as $type) {
                    echo sprintf("     ┃ %s\n", $type->value);
                }
            }

            echo "\n";
        }

        self::printLine();
    }

    /**
     * Пути-кандидаты для поиска файла в порядке приоритета:
     * корень проекта, в который подключён пакет, и его папка dev/.
     *
     * @return list<string>
     */
    public static function projectFileCandidates(string $fileName): array
    {
        $projectRoot = self::projectRoot();

        return [
            $projectRoot . '/' . $fileName,
            $projectRoot . '/dev/' . $fileName,
        ];
    }

    /**
     * Корень проекта, в который подключён пакет.
     *
     * При установке как зависимость путь пакета оканчивается на известный
     * суффикс vendor/<vendor>/<package> — отбрасываем его строго на этом уровне.
     * При запуске из самого пакета (standalone) — корень самого пакета.
     */
    public static function projectRoot(): string
    {
        $packageRoot = dirname(__DIR__, 2);
        $vendorSuffix = '/vendor/max-messenger-bot/max-bot-api-php';

        if (str_ends_with($packageRoot, $vendorSuffix)) {
            return substr($packageRoot, 0, -strlen($vendorSuffix));
        }

        return $packageRoot;
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
     * Запрос токена доступа у пользователя
     *
     * Сначала пытается получить токен из файла .tokens,
     * если не удалось - запрашивает вручную с проверкой.
     *
     * @return non-empty-string
     */
    public static function requestAccessToken(): string
    {
        self::printDoubleLine(false, true);
        echo "ШАГ 1: Токен доступа\n";
        self::printDoubleLine(true);

        // Попытка получить токен из файла .tokens
        $accessToken = self::getAccessTokenFromTokensFile();

        if ($accessToken !== null) {
            // Проверка токена доступа
            if (self::validateAccessToken($accessToken)) {
                return $accessToken;
            }
            echo "⚠️  Токен не прошёл проверку, введите другой\n";
        }

        echo "Введите ваш токен доступа Max Bot\n";
        echo "Получить токен можно через @MasterBot\n";

        $accessToken = self::readInput("\nТокен доступа: ");

        if (strlen($accessToken) < 10) {
            echo "❌ Слишком короткий токен доступа\n";

            return self::requestAccessToken();
        }

        // Проверка токена доступа
        if (!self::validateAccessToken($accessToken)) {
            return self::requestAccessToken();
        }

        return $accessToken;
    }

    /**
     * Проверка токена доступа через вызов getMyInfo()
     *
     * @param non-empty-string $accessToken Токен доступа для проверки
     * @return bool true если токен валиден
     */
    public static function validateAccessToken(string $accessToken): bool
    {
        echo "\n🔄 Проверка токена доступа... ";

        try {
            $client = new MaxApiClient($accessToken);
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
     * Разбор содержимого конфигурационного файла
     *
     * @return array<non-empty-string, non-empty-string> Ассоциативный массив [имя => значение]
     */
    private static function parseConfig(string $content): array
    {
        $values = [];
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));

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
            $value = trim($parts[1]);

            if ($name !== '' && $value !== '') {
                $values[$name] = $value;
            }
        }

        return $values;
    }

    /**
     * Подсказка, когда файл .tokens не найден ни в одном из мест
     *
     * @param list<string> $candidates
     */
    private static function printTokensFileNotFound(array $candidates): void
    {
        $example = dirname(__DIR__, 2) . '/dev/' . self::TOKENS_NAME . '.example';
        $projectRoot = self::projectRoot();

        echo "\n";
        echo "ℹ️  Файл .tokens не найден\n";
        echo "   Искал в:\n";
        foreach ($candidates as $candidate) {
            echo sprintf("     • %s\n", $candidate);
        }
        echo "\n";
        echo "   Чтобы не вводить токен каждый раз, создайте его в корне проекта\n";
        echo "   или в папке dev/ на основе примера:\n";
        echo "\n";
        echo sprintf("      cp %s %s\n", $example, $projectRoot . '/' . self::TOKENS_NAME);
        echo "      # или\n";
        echo sprintf("      cp %s %s\n", $example, $projectRoot . '/dev/' . self::TOKENS_NAME);
        echo "\n";
        echo "   Формат файла — одна запись на строку: token-name=access-token\n";
        echo "\n";
    }

    /**
     * Выбор токена из списка
     *
     * @param array<non-empty-string, non-empty-string> $tokens Ассоциативный массив [имя => токен]
     * @return non-empty-string|null Выбранный токен или null, если пользователь хочет ввести свой
     */
    private static function selectTokenFromList(array $tokens): ?string
    {
        echo "\n";
        echo "Доступные токены:\n";
        self::printLine();

        $names = array_keys($tokens);
        foreach ($names as $index => $name) {
            printf("  %d. %s\n", $index + 1, $name);
        }

        self::printLine();
        echo 'Введите номер токена для выбора (или 0 для ввода своего токена): ';

        $input = fgets(STDIN);

        if ($input === false) {
            return null;
        }

        $input = trim($input);

        // Если ввели 0 - ввод своего токена
        if ($input === '0') {
            return null;
        }

        // Проверка: ввод должен быть числом
        if (!preg_match('/^\d+$/', $input)) {
            echo "❌ Ошибка: введите номер токена\n";

            return null;
        }

        $selectedIndex = (int) $input - 1;

        if (!isset($names[$selectedIndex])) {
            echo "❌ Неверный номер токена\n";

            return null;
        }

        $selectedName = $names[$selectedIndex];
        $selectedToken = $tokens[$selectedName];

        echo sprintf("✓ Выбран токен: %s (***%s)\n", $selectedName, substr($selectedToken, -4));

        return $selectedToken;
    }
}
