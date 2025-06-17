# Max Bot & Max API Client для PHP

## Обзор проекта

Неофициальная PHP-библиотека для работы с Max Messenger Bot & API. Библиотека предоставляет клиент для взаимодействия с платформой Max через REST API.

**Статус:** Находится на стадии разработки и тестирования, большинство функций реализовано.

**Последняя известная версия API Max:** schemes/schema_2026_03_16.yaml

## Технологии

- **Язык:** PHP 8.2+
- **Автозагрузка:** PSR-4
- **Лицензия:** BSD-3-Clause
- **Зависимости:**
    - `mj4444/simple-http-client` ^0.1.1 — HTTP-клиент для выполнения запросов
    - `ext-mbstring` — расширение для работы с многобайтовыми строками
- **Dev-зависимости:**
    - `vimeo/psalm` ^6.15 — статический анализатор кода

## Структура проекта

```
repo/
├── src/                          # Исходный код библиотеки
│   ├── Contracts/                # Интерфейсы
│   │   ├── MaxApiConfigInterface.php
│   │   └── ModelInterface.php
│   ├── Exceptions/               # Исключения
│   │   ├── Validation/           # Исключения валидации
│   │   ├── ActionProhibited.php
│   │   ├── EventException.php
│   │   ├── LogicException.php
│   │   ├── RequireArgumentException.php
│   │   ├── RequireArgumentsException.php
│   │   ├── RuntimeException.php
│   │   └── SimpleQueryError.php
│   ├── HttpClient/               # HTTP-клиент для API-запросов
│   │   └── Exceptions/           # Исключения HTTP-клиента
│   ├── MaxBot/                   # Вспомогательные классы бота
│   │   └── Events/               # События бота
│   ├── Models/                   # Модели данных
│   │   ├── Enums/                # Перечисления
│   │   ├── Requests/             # Модели запросов
│   │   └── Responses/            # Модели ответов
│   ├── MaxApiClient.php          # Основной API-клиент
│   ├── MaxApiConfig.php          # Конфигурация API
│   └── MaxBot.php                # Класс бота с обработкой команд
├── bin/                          # Исполняемые скрипты
│   ├── max-debug.php
│   ├── max-subscribe.php
│   ├── max-subscribes.php
│   └── max-unsubscribe.php
├── dev/                          # Утилиты для разработки
├── docs/                         # Документация
│   ├── ClassLists/               # Списки классов
│   ├── Examples/                 # Примеры кода
│   ├── Extended/                 # Расширенная документация
│   └── Internal/                 # Внутренняя документация
├── schemes/                      # Схемы API (JSON/YAML)
│   ├── schema_2026_03_16.json
│   └── schema_2026_03_16.yaml
├── test/                         # Тесты
│   └── test.php                  # Пример использования
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

На данный момент тестов нет.

### Исполняемые скрипты

В директории `bin/` находятся скрипты для отладки и управления подписками:

- `max-debug` — мониторинг обновлений на сервере
- `max-subscribe` — подписка на обновления
- `max-subscribes` — просмотр подписок
- `max-unsubscribe` — отписка от обновлений

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
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$bot = new MaxBot('your-access-token');

// Добавление обработчика команды
$bot->getCommandHandler()
    ->onCommand('start', function (MessageCreatedEvent $event) {
        // Обработка команды /start
    });

// Обработка сообщений
$bot->onMessageCreated(function (MessageCreatedEvent $event) {
    // Обработка нового сообщения
});

// Запуск обработки обновлений с сервера
$marker = null;
while (true) {
    $marker = $bot->handleFromServer(marker: $marker);
    usleep(100);
}
```

## Соглашения

### Код-стайл

- **Базовый стандарт:** PSR-12
- **Кодировка:** UTF-8
- **Концы строк:** LF
- **Отступы:** 4 пробела (2 пробела для YAML)
- **Завершающая новая строка:** Да
- **Удаление пробелов в конце строк:** Да
- **Максимальная длина строки:** 120 символов (если возможно)

### Типизация

Все файлы используют строгую типизацию:

```php
declare(strict_types=1);
```

Все параметры и возвращаемые значения должны иметь типы. Используйте `mixed` только когда действительно необходимо.

### Пространства имён

- Основной namespace: `MaxMessenger\Bot`
- Автозагрузка PSR-4: `src/` → `MaxMessenger\Bot\`

### Именование

- **Интерфейсы:** Суффикс `Interface`
- **Исключения:** Суффикс `Exception`

### Архитектурные паттерны

- **PSR-4** для автозагрузки классов
- **Контракты (интерфейсы)** для абстракции зависимостей
- **Value Objects** для конфигурации (например, `MaxApiConfig`)
- **DTO** для моделей запросов и ответов
- **SensitiveParameter** для защиты чувствительных данных (токены доступа)
- **SensitiveParameterValue** для хранения чувствительных значений
- **Readonly свойства** используются, когда это возможно
- **Статические методы** должны вызываться как статические методы, используя ключевое слово `static`

### Модели

- `BaseRequestModel` — базовый класс для моделей запросов
- `BaseResponseModel` — базовый класс для моделей ответов
- Модели запросов находятся в `Models/Requests/` без вложенных папок
- Модели ответов находятся в `Models/Response/` без вложенных папок
- Перечисления находятся в `Models/Enums/` без вложенных папок
- При генерации моделей используется схема из `schemes/`
- Используются оригинальные описания из схемы с исходным форматированием

### Документирование кода

- DocBlocks обязательны для всех публичных методов
- Для публичных методов добавляйте тег `@api`
- Описывайте назначение метода, параметры и возвращаемое значение
- Используйте аннотации Psalm: `@psalm-suppress`, `@psalm-var`, `@template`
- Краткое содержание (summary) по возможности должно отвечать на вопрос «Что сделать?»
- Если функция возвращает `$this`, это обязательно нужно документировать в phpdoc: `@return $this`

### Терминология

- **Бот** — объект класса `\MaxMessenger\Bot\MaxBot`
- **API-клиент** — объект класса `\MaxMessenger\Bot\MaxApiClient`
- **Конфигурация** — объект класса `\MaxMessenger\Bot\MaxApiConfig`
- **Обновление** — объект класса `\MaxMessenger\Bot\Models\Responses\Update`
- **Тип обновления** — значение в поле `update_type` объекта обновления
- **Событие** — объект класса, унаследованного от `\MaxMessenger\Bot\MaxBot\Events\BaseEvent`
- **Модель запроса** — класс, унаследованный от `\MaxMessenger\Bot\Models\Requests\BaseRequestModel`
- **Модель ответа** — класс, унаследованный от `\MaxMessenger\Bot\Models\Response\BaseResponseModel`

## API Reference

### MaxApiClient

Основной клиент для взаимодействия с API Max. Предоставляет методы для:

- Управления чатами (создание, редактирование, удаление)
- Управления участниками (добавление, удаление, назначение администраторов)
- Отправки и редактирования сообщений
- Получения обновлений через long polling
- Управления подписками на WebHook
- Загрузки файлов

### MaxBot

Класс для создания ботов с поддержкой обработки команд и событий:

- Обработка команд через `CommandHandler`
- Обработка событий (сообщения, callback, изменения чатов)
- Поддержка цепочек обработчиков (prepare, event, fallback, final)
- Обработка исключений
- Получение обновлений с сервера или из глобального контекста (WebHook)

### MaxApiConfig

Конфигурация подключения:

- `accessToken` — токен доступа бота (SensitiveParameterValue)
- `baseUrl` — базовый URL API (по умолчанию: `https://platform-api.max.ru`)
- `httpClient` — HTTP-клиент (по умолчанию: `CurlHttpClient`)

## Исключения

### Основные исключения

- `ActionProhibited` — запрещённое действие
- `EventException` — исключение события (позволяет продолжить или остановить обработку)
- `LogicException` — ошибки логики
- `RequireArgumentException` / `RequireArgumentsException` — ошибки обязательного аргумента
- `RuntimeException` — ошибки времени выполнения
- `SimpleQueryError` — ошибки выполнения запросов к API

### Исключения валидации

Находятся в `Exceptions/Validation/`:

- `MatchException` — несоответствие шаблону
- `ValidateException` — базовое исключение валидации

### HTTP-исключения

- `HttpClient\Exceptions\AccessTokenException` — проблемы с токеном доступа
- `HttpClient\Exceptions\UnexpectedFormatException` — неожиданный формат ответа

## Документация

Основная документация находится в директории `docs/`:

- [Начало работы](docs/GettingStarted.md)
- [Соглашения](docs/Conventions.md)
- [Отправка сообщений](docs/SendingMessages.md)
- [Обработка обновлений](docs/ProcessingUpdates.md)
- [API-клиент](docs/ApiClient.md)
- [Список документации](docs/DocumentationList.md)

## Схемы API

Схемы API хранятся в директории `schemes/` в форматах JSON и YAML. Используются для генерации моделей запросов и ответов.
