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
│   ├── max-chats.php             # Просмотр списка чатов
│   ├── max-debug.php             # Мониторинг обновлений
│   ├── max-delete-chats.php      # Удаление пустых чатов
│   ├── max-subscribe.php         # Подписка на обновления
│   ├── max-subscribes.php        # Просмотр подписок
│   ├── max-unsubscribe.php       # Отписка от обновлений
│   └── utils.php                 # Утилиты для скриптов
├── dev/                          # Утилиты для разработки
├── docs/                         # Документация
│   ├── ClassLists/               # Списки классов
│   │   ├── Contracts.md
│   │   ├── Enums.md
│   │   ├── Exceptions.md
│   │   ├── HttpClient.md
│   │   ├── MaxBotEvents.md
│   │   ├── OtherClasses.md
│   │   ├── RequestModels.md
│   │   └── ResponseModels.md
│   ├── Examples/                 # Примеры кода
│   │   ├── AddingHandlers.md
│   │   ├── CustomeRquest.md
│   │   ├── Initialization.md
│   │   ├── ProcessingUpdates.md
│   │   └── README.md
│   ├── Extended/                 # Расширенная документация
│   ├── Internal/                 # Внутренняя документация
│   └── VerySimpleBot.md          # Пример создания простого бота
├── schemas/                      # Схемы API (JSON/YAML)
│   ├── schema_2026_03_16.json
│   ├── schema_2026_03_16.yaml
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
    usleep(100000);
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
- Модели ответов находятся в `Models/Responses/` без вложенных папок
- Перечисления находятся в `Models/Enums/` без вложенных папок
- При генерации моделей используется схема из `schemas/`
- Используются оригинальные описания из схемы с исходным форматированием

### Документирование кода

- DocBlocks обязательны для всех публичных методов
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

### Списки классов

- [Интерфейсы](docs/ClassLists/Contracts.md)
- [Перечисления](docs/ClassLists/Enums.md)
- [Исключения](docs/ClassLists/Exceptions.md)
- [HTTP-клиент](docs/ClassLists/HttpClient.md)
- [События MaxBot](docs/ClassLists/MaxBotEvents.md)
- [Другие классы](docs/ClassLists/OtherClasses.md)
- [Модели запросов](docs/ClassLists/RequestModels.md)
- [Модели ответов](docs/ClassLists/ResponseModels.md)

### Примеры

- [Инициализация](docs/Examples/Initialization.md)
- [Добавление обработчиков](docs/Examples/ProcessingEvents.md)
- [Обработка обновлений](docs/Examples/ProcessingUpdates.md)
- [Пользовательский запрос](docs/Examples/CustomeRquest.md)

## Схемы API

- Схемы API хранятся в директории `schemas/` в форматах JSON и YAML. Используй схему `schema_current.yaml`
- При переносе документации из схемы в phpdoc:
    - Нужно экранировать символ `\`
    - Не нужно ничего вырезать (например символ '\`')  
