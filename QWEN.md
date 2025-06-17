# Max Bot & Max API Client для PHP

## Обзор проекта

Неофициальная PHP-библиотека для работы с Max Messenger Bot & API. Библиотека предоставляет клиент для взаимодействия с платформой Max через REST API.

**Статус:** Находится на стадии разработки и тестирования, большинство функций реализовано.

**Последняя известная версия API Max:** 0.0.10

## Технологии

- **Язык:** PHP 8.2+
- **Автозагрузка:** PSR-4
- **Лицензия:** BSD-3-Clause
- **Зависимости:**
    - `mj4444/simple-http-client` ^0.1.0 — HTTP-клиент для выполнения запросов
    - `ext-mbstring` — расширение для работы с многобайтовыми строками
- **Dev-зависимости:**
    - `psr/container` ^2.0 — контейнер зависимостей
    - `vimeo/psalm` ^6.15 — статический анализатор кода

## Структура проекта

```
repo/
├── src/                          # Исходный код библиотеки
│   ├── MaxApiClient.php          # Основной API-клиент
│   ├── MaxBot.php                # Класс бота с обработкой команд
│   ├── MaxApiConfig.php          # Конфигурация API
│   ├── Contracts/                # Интерфейсы
│   │   ├── CommandClassInterface.php
│   │   ├── MaxApiConfigInterface.php
│   │   └── ModelInterface.php
│   ├── Exceptions/               # Исключения
│   │   ├── Validation/
│   │   ├── ActionProhibited.php
│   │   ├── LogicException.php
│   │   ├── RequireArgumentException.php
│   │   ├── RuntimeException.php
│   │   ├── SimpleQueryError.php
│   │   └── ValidateException.php
│   ├── HttpClient/               # HTTP-клиент для API-запросов
│   └── Models/                   # Модели данных
│       ├── Enums/                # Перечисления
│       ├── Requests/             # Модели запросов
│       └── Response/             # Модели ответов
├── test/                         # Тесты
│   └── test.php                  # Пример использования
├── dev/                          # Утилиты для разработки
│   └── format-swagger-json.php
├── docs/                         # Документация
├── schemes/                      # Схемы API (YAML)
│   └── schema_0_0_10.yaml
├── composer.json                 # Конфигурация Composer
├── psalm.xml                     # Конфигурация статического анализатора Psalm
└── .editorconfig                 # Настройки форматирования кода
```

## Установка

```bash
composer require max-messenger-bot/max-bot-api-php
```

## Сборка и запуск

### Установка зависимостей

```bash
composer install
```

### Статический анализ

```bash
# Запуск Psalm (уровень строгости: 1)
vendor/bin/psalm
```

### Тестирование

На данный момент тесты находятся в зачаточном состоянии. В директории `test/` присутствует пример использования (`test.php`).

## Примеры использования

### Создание API-клиента

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;

// Простая инициализация с токеном
$client = new MaxApiClient('your-access-token');

// Или с конфигурацией
$config = new MaxApiConfig(
    accessToken: 'your-access-token',
    baseUrl: 'https://platform-api.max.ru'
);
$client = new MaxApiClient($config);
```

### Создание бота

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token');

// Добавление команды
$bot->addCommand('start', function ($command, $bot) {
    // Обработка команды
});
```

### Основные методы API-клиента

- `getMyInfo()` — получение информации о боте
- `getSubscriptions()` — получение списка подписок
- `addMembers($chatId, $userIdsList)` — добавление участников в чат
- `deleteChat($chatId)` — удаление чата
- `deleteMessage($messageId)` — удаление сообщения

## Конвенции разработки

### Код-стайл

- **Кодировка:** UTF-8
- **Концы строк:** LF
- **Отступы:** 4 пробела (2 пробела для YAML)
- **Завершающая новая строка:** Да
- **Удаление пробелов в конце строк:** Да

### Типизация

Все файлы используют строгую типизацию:

```php
declare(strict_types=1);
```

### Пространства имён

- Основной namespace: `MaxMessenger\Bot`
- Автозагрузка PSR-4: `src/` → `MaxMessenger\Bot\`

### Архитектурные паттерны

- **PSR-4** для автозагрузки классов
- **Контракты (интерфейсы)** для абстракции зависимостей
- **Value Objects** для конфигурации (например, `MaxApiConfig`)
- **DTO** для моделей запросов и ответов
- **SensitiveParameter** для защиты чувствительных данных (токены доступа)
- **Статические методы** должны вызываться как статические методы, используя ключевое слово static.

### Модели

- `BaseRequestModel` — базовый класс для моделей запросов
- `BaseResponseModel` — базовый класс для моделей ответов, если модель не наследует другую модель ответа
- Модели запросов находятся в `Models/Requests/` без вложенных папок
- Модели ответов находятся в `Models/Response/` без вложенных папок
- Перечисления находятся в `Models/Enums/` без вложенных папок
- При работе с моделями запросов, изучи @src/Models/Requests/QWEN.md
- При работе с моделями ответов, изучи @src/Models/Response/QWEN.md
- При работе с перечислениями, изучи @src/Models/Enums/QWEN.md
- Для генерации моделей используй схему @schemes/schema_0_0_10.yaml
- Используются оригинальные описания из схемы с исходным форматированием, без перевода на русский язык
- При обновлении моделей, нужно переписывать код использующий методы отмеченные как `deprecated`

### Документирование кода

- Если функция возвращает $this, это нужно документировать в phpdoc

### Исправление проблем

- При отображении результата в командной строке, нужно в отображаемых данных таблиц символ `|` заменять на `┃`

## API Reference

### MaxApiClient

Основной клиент для взаимодействия с API Max.

### MaxBot

Класс для создания ботов с поддержкой обработки команд через контейнер зависимостей (PSR-11).

### MaxApiConfig

Конфигурация подключения:

- `accessToken` — токен доступа бота
- `baseUrl` — базовый URL API (по умолчанию: `https://platform-api.max.ru`)
- `httpClient` — HTTP-клиент (по умолчанию: `CurlHttpClient`)

## Исключения

- `ActionProhibited` — запрещённое действие
- `LogicException` — ошибки логики
- `RequireArgumentException` — ошибки обязательного аргумента
- `RuntimeException` — ошибки времени выполнения
- `SimpleQueryError` — ошибки выполнения запросов к API
- `ValidateException` — ошибки валидации
- **Validation/** — подпространство исключений валидации
