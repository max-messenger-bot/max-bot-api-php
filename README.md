# Max Bot & Max Api Client For PHP

> [!NOTE]
> Это неофициальные Бот и API.
> Бот и API находится на стадии разработки и тестирования, большинство функций реализовано.
> Статус проверки смотрите в документации.

## Установка

```bash
composer require max-messenger-bot/max-bot-api-php
```

### Требования

- PHP 8.2+
- Расширение `ext-mbstring`

### Зависимости

- `mj4444/simple-http-client` ^0.1.1 — HTTP-клиент для выполнения запросов

## Примеры

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

// Добавление обработчика команд
$commands = (new CommandHandler())
    // Добавление обработчика команды
    ->onCommand('start', function (MessageCreatedEvent $event): void {
        // Обработка
    })
    // Добавление обработчика любой команды
    ->onCommands(function (MessageCreatedEvent $event): void {
        // Обработка
    });

$bot->onMessageCreated($commands->handle(...));

// Добавление обработчика нового сообщения
$bot->onMessageCreated(function (MessageCreatedEvent $event) {
    // Обработка
});

$bot->handleFromGlobal();
```

Обработка событий при разработке:

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token');

// Добавление обработчиков

while (true) {
    $marker = $bot->handleFromServer();
    echo sprintf('%s: Marker: %s' . PHP_EOL, date('Y-m-d H:i:s'), $marker ?? '[null]');
    usleep(100);
}
```

## Документация

- [Интерфейсы](docs/Contracts.md)
- [Перечисления](docs/Enums.md)
- [Классы исключений](docs/Exceptions.md)
- [HTTP-клиент](docs/HttpClient.md)
- [События MaxBot](docs/MaxBotEvents.md)
- [Другие классы](docs/OtherClasses.md)
- [Модели запросов](docs/RequestModels.md)
- [Модели ответов](docs/ResponseModels.md)
- [Список методов MaxApiClient из схемы](docs/SchemaMethods.md)

Последняя известная версия схемы OpenAPI API Max: 0.0.10 ([yaml](./schemes/schema_0_0_10.yaml)) - Устарела. 
