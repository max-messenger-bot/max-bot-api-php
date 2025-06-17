<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MaxMessenger\Bot\Exceptions\SimpleQueryError;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Enums\UpdateType;
use MaxMessenger\Bot\Models\Requests\SubscriptionRequestBody;

/**
 * Консольная команда для настройки Webhook подписки Max Bot API
 *
 * Запрашивает у пользователя:
 * - API key (токен доступа)
 * - URL адрес Webhook
 * - Типы обновлений для получения
 * - Кодовое слово (secret)
 */
class MaxSubscribe
{

    /**
     * Основная функция
     */
    public static function main(): void
    {
        echo "\n";
        echo sprintf("%s\n", str_repeat('█', 50));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("█%s█\n", mb_str_pad('Настройка Webhook подписки Max Bot API', 48, ' ', STR_PAD_BOTH));
        echo sprintf("█%s█\n", str_repeat(' ', 48));
        echo sprintf("%s\n", str_repeat('█', 50));

        // Шаг 1: API Key
        $apiKey = self::requestApiKey();
        echo "✓ API key принят\n";

        // Шаг 2: URL Webhook
        $url = self::requestWebhookUrl();
        echo sprintf("✓ URL принят: %s\n", $url);

        // Шаг 3: Выбор типов обновлений
        $updateTypes = self::selectUpdateTypes();
        if ($updateTypes === true) {
            echo "✓ Выбраны все типы обновлений\n";
        } else {
            echo sprintf("✓ Выбрано типов обновлений: %d\n", count($updateTypes));
        }

        // Шаг 4: Кодовое слово
        /** @var non-empty-string */
        $secret = self::requestSecret();
        echo sprintf("✓ Кодовое слово принято (длина: %d символов)\n", strlen($secret));

        // Создание API клиента
        $client = new MaxApiClient($apiKey);

        // Сохранение подписки на сервер
        if (!self::saveSubscription($client, $url, $secret, $updateTypes)) {
            echo "\n❌ Не удалось сохранить подписку\n";
            exit(1);
        }

        // Проверка сохранённой подписки
        if (!self::verifySubscription($client, $url, $updateTypes)) {
            echo "\n❌ Не удалось подтвердить подписку\n";
            exit(1);
        }

        // Вывод итоговой информации
        echo "\n";
        echo sprintf("%s\n", str_repeat('─', 50));
        echo "Итоговая конфигурация:\n";
        echo sprintf("%s\n", str_repeat('─', 50));
        echo sprintf("URL: %s\n", $url);
        if ($updateTypes === true) {
            echo "Типы обновлений: Все\n";
        } else {
            echo sprintf("Типы обновлений: %s\n", implode(', ', $updateTypes));
        }
        echo sprintf("Кодовое слово: %s\n", str_repeat('*', min(strlen($secret), 10)));
        echo sprintf("%s\n", str_repeat('─', 50));

        echo "\n✅ Webhook подписка успешно настроена!\n\n";
    }

    /**
     * Отображение списка типов обновлений
     */
    private static function displayUpdateTypes(): void
    {
        echo "\nДоступные типы обновлений:\n";
        echo sprintf("%s\n", str_repeat('─', 50));

        $cases = UpdateType::cases();
        foreach ($cases as $index => $case) {
            printf("  %2d. %-30s %s\n", $index, $case->value, $case->name);
        }

        echo sprintf("%s\n", str_repeat('─', 50));
    }

    /**
     * Валидация кодового слова (5-256 символов, только A-Z, a-z, 0-9, _ и -)
     */
    private static function isValidSecret(string $secret): bool
    {
        if (strlen($secret) < 5 || strlen($secret) > 256) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9_-]+$/', $secret) === 1;
    }

    /**
     * Валидация URL (только https, без порта)
     */
    private static function isValidUrl(string $url): bool
    {
        if (!str_starts_with($url, 'https://')) {
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parsedUrl = parse_url($url);

        // URL не должен содержать порт
        if (isset($parsedUrl['port'])) {
            return false;
        }

        return true;
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
     * Запрос кодового слова
     */
    private static function requestSecret(): string
    {
        echo sprintf("\n%s\n", str_repeat('═', 50));
        echo "ШАГ 2: Кодовое слово (Secret)\n";
        echo sprintf("%s\n\n", str_repeat('═', 50));

        echo "Введите кодовое слово для проверки подлинности запросов\n";
        echo "Требования:\n";
        echo "  - Длина: от 5 до 256 символов\n";
        echo "  - Разрешённые символы: A-Z, a-z, 0-9, _ и -\n";
        echo "  - Будет отправляться в заголовке X-Max-Bot-Api-Secret\n";

        $secret = self::readInput("\nКодовое слово: ");

        if (!self::isValidSecret($secret)) {
            echo "❌ Кодовое слово не соответствует требованиям\n";
            return self::requestSecret();
        }

        return $secret;
    }

    /**
     * Запрос URL Webhook
     *
     * @return non-empty-string
     */
    private static function requestWebhookUrl(): string
    {
        echo sprintf("\n%s\n", str_repeat('═', 50));
        echo "ШАГ 2: URL адрес Webhook\n";
        echo sprintf("%s\n\n", str_repeat('═', 50));

        echo "Введите URL вашего Webhook endpoint\n";
        echo "Требования:\n";
        echo "  - Протокол: только https\n";
        echo "  - Порт: не указывать (используется стандартный 443)\n";
        echo "Пример: https://your-domain.com/webhook/max\n";

        $url = self::readInput("\nURL: ");

        if (!self::isValidUrl($url)) {
            echo "❌ Неверный формат URL (должен быть https:// без порта)\n";
            return self::requestWebhookUrl();
        }

        return $url;
    }

    /**
     * Сохранение подписки на сервер через API
     *
     * @param MaxApiClient $client API клиент
     * @param non-empty-string $url URL Webhook
     * @param non-empty-string $secret Секретное слово
     * @param list<string>|true $updateTypes Типы обновлений или true для всех типов
     * @return bool true при успешном сохранении
     */
    private static function saveSubscription(
        MaxApiClient $client,
        string $url,
        string $secret,
        array|true $updateTypes
    ): bool {
        echo "\n🔄 Сохранение подписки на сервер... ";

        try {
            // Если выбраны все типы обновлений (true), передаём пустой список
            $typesToSave = $updateTypes === true ? [] : $updateTypes;

            // Преобразуем строки в UpdateType enum
            $updateTypeEnums = !empty($typesToSave)
                ? UpdateType::tryFromList($typesToSave)
                : [];

            $subscription = SubscriptionRequestBody::new(
                url: $url,
                secret: $secret,
                update_types: !empty($updateTypeEnums) ? $updateTypeEnums : null
            );

            $client->subscribe($subscription);
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
     * Выбор типов обновлений
     *
     * @return list<string>|true Список типов обновлений или true для всех типов
     */
    private static function selectUpdateTypes(): array|true
    {
        self::displayUpdateTypes();

        echo "\nВведите номера типов обновлений через запятую (например: 0,1,2,8)\n";
        echo "Или введите 'all' для выбора всех типов: ";

        $input = fgets(STDIN);

        if ($input === false) {
            exit(1);
        }

        $input = trim($input);

        if (strtolower($input) === 'all') {
            return true;
        }

        // Проверка: ввод должен содержать только цифры и запятые
        if (!preg_match('/^[0-9,]+$/', $input)) {
            echo "❌ Ошибка: ввод должен содержать только цифры и запятые\n";
            return self::selectUpdateTypes();
        }

        $cases = UpdateType::cases();
        $selectedIndices = array_map('trim', explode(',', $input));
        $selectedTypes = [];
        $hasError = false;

        foreach ($selectedIndices as $index) {
            if ($index === '') {
                continue;
            }
            if (isset($cases[(int)$index])) {
                $selectedTypes[] = $cases[(int)$index]->value;
            } else {
                echo "⚠️  Неверный индекс: {$index}\n";
                $hasError = true;
            }
        }

        if ($hasError || empty($selectedTypes)) {
            echo "❌ Не выбрано ни одного типа обновлений\n";
            return self::selectUpdateTypes();
        }

        // Вывод списка выбранных типов обновлений
        echo "\n✅ Выбраны типы обновлений:\n";
        echo sprintf("%s\n", str_repeat('─', 50));
        foreach ($selectedTypes as $type) {
            echo sprintf("  • %s\n", $type);
        }
        echo sprintf("%s\n", str_repeat('─', 50));

        return $selectedTypes;
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

    /**
     * Проверка сохранённой подписки
     *
     * @param MaxApiClient $client API клиент
     * @param non-empty-string $url URL Webhook
     * @param list<string>|true $updateTypes Типы обновлений или true для всех типов
     * @return bool true если данные совпадают
     */
    private static function verifySubscription(
        MaxApiClient $client,
        string $url,
        array|true $updateTypes
    ): bool {
        echo "\n🔄 Проверка сохранённой подписки... ";

        try {
            $result = $client->getSubscriptions();
            $subscriptions = $result->getSubscriptions();

            if (empty($subscriptions)) {
                echo "❌ Подписки не найдены\n";
                return false;
            }

            // Ищем нашу подписку по URL
            $foundSubscription = null;
            foreach ($subscriptions as $subscription) {
                if ($subscription->getUrl() === $url) {
                    $foundSubscription = $subscription;
                    break;
                }
            }

            if ($foundSubscription === null) {
                echo "❌ Подписка с указанным URL не найдена\n";
                return false;
            }

            // Проверяем типы обновлений
            $savedTypes = $foundSubscription->getUpdateTypes();
            $savedTypeValues = $savedTypes !== null
                ? array_map(static fn(UpdateType $type): string => $type->value, $savedTypes)
                : [];

            // Если выбраны все типы (true), сервер может вернуть пустой список или null
            if ($updateTypes === true) {
                $allTypes = array_map(static fn(UpdateType $case): string => $case->value, UpdateType::cases());
                if ($savedTypeValues === [] || $savedTypeValues === $allTypes) {
                    echo "✅ Подтверждено (все типы)\n";
                    return true;
                }
                echo "❌ Типы обновлений не совпадают\n";
                return false;
            }

            // Сравниваем списки
            $expectedTypes = $updateTypes;
            sort($expectedTypes);
            sort($savedTypeValues);

            if ($expectedTypes === $savedTypeValues) {
                echo "✅ Подтверждено\n";
                return true;
            }

            echo "❌ Типы обновлений не совпадают\n";
            echo sprintf("  Ожидалось: %s\n", implode(', ', $expectedTypes));
            echo sprintf("  Получено: %s\n", implode(', ', $savedTypeValues));
            return false;
        } catch (SimpleQueryError $e) {
            echo sprintf('❌ Ошибка API: %s\n', $e->getMessage());
            return false;
        } catch (Throwable $e) {
            echo sprintf('❌ Ошибка: %s\n', $e->getMessage());
            return false;
        }
    }
}

// Запуск
MaxSubscribe::main();
