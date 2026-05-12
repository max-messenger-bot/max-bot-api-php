# Max Bot & Max API Client для PHP

## Обзор проекта

Неофициальная PHP-библиотека для работы с Max Messenger Bot & API. Библиотека предоставляет клиент для взаимодействия с платформой Max через REST API.

**Статус:** Находится на стадии разработки и тестирования, большинство функций реализовано.

**Текущая версия API Max:** schemas/schema_current.yaml

## Технологии

- **Язык:** PHP 8.2+
- **Автозагрузка:** PSR-4
- **Лицензия:** BSD-3-Clause
- **Зависимости:**
    - `mj4444/simple-http-client` ^0.2.1 — HTTP-клиент для выполнения запросов
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
│   │   ├── MaxBot/               # Исключения MaxBot
│   │   │   ├── Events/           # Исключения событий бота
│   │   │   └── Update/           # Исключения обновлений
│   │   ├── Validation/           # Исключения валидации
│   │   ├── ActionProhibited.php
│   │   ├── LogicException.php
│   │   ├── RequiredArgumentException.php
│   │   ├── RequiredArgumentsException.php
│   │   ├── RuntimeException.php
│   │   └── SimpleQueryError.php
│   ├── HttpClient/               # HTTP-клиент для API-запросов
│   │   ├── Body/                 # Тела HTTP-запросов
│   │   ├── Exceptions/           # Исключения HTTP-клиента
│   │   │   ├── HttpResponse/     # HTTP-ответ исключения
│   │   │   │   └── Http/         # HTTP-исключения Max API
│   │   │   ├── AccessTokenException.php
│   │   │   └── UnexpectedFormatException.php
│   │   ├── JsonRequest.php       # JSON HTTP-запрос
│   │   ├── JsonResponse.php      # JSON HTTP-ответ
│   │   └── MaxHttpClient.php     # HTTP-клиент для API Max
│   ├── MaxBot/                   # Вспомогательные классы бота
│   │   ├── Events/               # События бота
│   │   ├── CallbackHandler.php   # Обработчик callback-запросов
│   │   ├── CallbackJsonHandler.php # Обработчик callback JSON
│   │   ├── CommandHandler.php    # Обработчик команд
│   │   └── HandlerListType.php   # Тип списка обработчиков
│   ├── Models/                   # Модели данных
│   │   ├── Enums/                # Перечисления
│   │   ├── Requests/             # Модели запросов
│   │   └── Responses/            # Модели ответов
│   ├── MaxApiClient.php          # Основной API-клиент
│   ├── MaxApiConfig.php          # Конфигурация API
│   └── MaxBot.php                # Класс бота с обработкой команд
├── bin/                          # Исполняемые скрипты
│   ├── max-chats                 # Просмотр списка чатов
│   ├── max-chats.php
│   ├── max-debug                 # Мониторинг обновлений
│   ├── max-debug.php
│   ├── max-delete-chats          # Удаление пустых чатов
│   ├── max-delete-chats.php
│   ├── max-subscribe             # Подписка на обновления
│   ├── max-subscribe.php
│   ├── max-subscribes            # Просмотр подписок
│   ├── max-subscribes.php
│   ├── max-unsubscribe           # Отписка от обновлений
│   ├── max-unsubscribe.php
│   └── utils.php                 # Утилиты для скриптов
├── dev/                          # Утилиты для разработки
├── docs/                         # Документация
│   ├── Examples/                 # Примеры кода
│   ├── images/                   # Изображения
│   ├── Internal/                 # Внутренняя документация
│   ├── RawModel.md               # Raw-модель запроса
│   └── VerySimpleBot.md          # Пример создания простого бота
├── schemas/                      # Схемы API (JSON/YAML)
│   ├── schema_2026_04_15.json
│   ├── schema_2026_04_15.yaml
│   └── schema_current.yaml
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

$bot = new MaxBot('your-access-token', 'your-secret');

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
    usleep(100000);
}
```

## Соглашения

Все соглашения по коду, стилю, документированию и терминологии описаны в файле [docs/Conventions.md](docs/Conventions.md).

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
- `LogicException` — ошибки логики
- `RequiredArgumentException` / `RequiredArgumentsException` — ошибки обязательного аргумента
- `RuntimeException` — ошибки времени выполнения
- `SimpleQueryError` — ошибки выполнения запросов к API

### Исключения MaxBot

- `MaxBot\Events\EventException` — служебное исключение для завершения обработки
- `MaxBot\Events\SenderUnknownException` — неизвестный отправитель события
- `MaxBot\Update\BadRequestException` — неверный запрос обновления
- `MaxBot\Update\InvalidSecretException` — неверный секрет
- `MaxBot\Update\NotMaxRequestException` — запрос не от Max
- `MaxBot\Update\UpdateRequestException` — базовый класс для исключений запросов обновлений

### Исключения валидации

Находятся в `Exceptions/Validation/`:

- `MatchException` — несоответствие шаблону
- `ValidateException` — базовое исключение валидации

### HTTP-исключения

- `HttpClient\Exceptions\AccessTokenException` — токен доступа не установлен
- `HttpClient\Exceptions\UnexpectedFormatException` — неожиданный формат ответа
- `HttpClient\Exceptions\HttpResponse\Http\MaxHttpException` — базовый класс для HTTP-исключений Max API
- `HttpClient\Exceptions\HttpResponse\Http\BadRequestException` — неверный запрос (400)
    - `isAttachmentNotReady(): bool` — проверяет, что ошибка связана с неготовностью вложения
- `HttpClient\Exceptions\HttpResponse\Http\UnauthorizedException` — неавторизован (401)
- `HttpClient\Exceptions\HttpResponse\Http\ForbiddenException` — доступ запрещён (403)
- `HttpClient\Exceptions\HttpResponse\Http\NotFoundException` — ресурс не найден (404)
- `HttpClient\Exceptions\HttpResponse\Http\NotAllowedException` — метод не разрешён (405)
- `HttpClient\Exceptions\HttpResponse\Http\TooManyRequestsException` — слишком много запросов (429)
- `HttpClient\Exceptions\HttpResponse\Http\InternalHttpException` — внутренняя ошибка сервера (500)
- `HttpClient\Exceptions\HttpResponse\Http\ServiceUnavailableException` — сервис недоступен (503)
- `HttpClient\Exceptions\HttpResponse\Http\UnknownException` — неизвестная ошибка сервера
- `HttpClient\Exceptions\HttpResponse\Http\UnsupportedMediaTypeException` — неподдерживаемый тип данных (415)
- `HttpClient\Exceptions\HttpResponse\UploadException` — ошибка загрузки файла

## Документация

Основная документация находится в директории `docs/`:

### Руководства

- [Начало работы](docs/GettingStarted.md)
- [Соглашения](docs/Conventions.md)
- [Отправка сообщений](docs/SendingMessages.md)
- [Обработка обновлений](docs/ProcessingUpdates.md)
- [Обработка команд](docs/ProcessingCommands.md)
- [Обработка событий](docs/ProcessingEvents.md)
- [API-клиент](docs/ApiClient.md)

### Примеры

- [Примеры инициализации API-клиента и бота](docs/Examples/Initialization.md)
- [Примеры отправки сообщений](docs/Examples/SendingMessages.md)
- [Примеры отправки действий](docs/Examples/SendingActions.md)
- [Примеры обработки команд](docs/Examples/ProcessingCommands.md)
- [Примеры обработки нажатий кнопок](docs/Examples/ProcessingCallbacks.md)
- [Примеры обработки событий](docs/Examples/ProcessingEvents.md)
- [Примеры обработки обновлений](docs/Examples/ProcessingUpdates.md)
- [Примеры пользовательского запроса](docs/Examples/CustomeRquest.md)
- [Примеры использования RawModel](docs/Examples/RawModel.md)

## Схемы API

- Схемы API хранятся в директории `schemas/` в форматах JSON и YAML. Используй схему `schema_current.yaml`
- При переносе документации из схемы в phpdoc:
    - Нужно экранировать символ `\`
    - Не нужно ничего вырезать (например символ '\`')  
