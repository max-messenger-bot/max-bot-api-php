# Max Bot & Max Api Client For PHP

> [!NOTE]
> Это неофициальные MAX API-клиент и бот.
> Проект находится на стадии разработки и тестирования, большинство функций реализовано, идёт тестирование.
> Статус проверки и тестирования смотрите в документации в разделе "Списки классов".

> [!WARNING]
> По поводу ошибок в клиенте, пожалуйста обращайтесь ко мне напрямую:
>   - Max: [Евгений](https://max.ru/u/f9LHodD0cOID7ezkLpMv_5wNX9YmRCmk-0bp4q1uWCRtrdClF9F21Buxhyk)
>   - Telegram: [mj4444ru](https://t.me/mj4444ru)

### Документация в коде

I believe that in-code documentation should be in English. However, due to a lack of resources to translate
the documentation into English, the in-code documentation is presented in Russian.

Я считаю, что документация в коде должна быть на английском языке. Однако из-за нехватки ресурсов для перевода
документации на английский язык, документация в коде представлена на русском языке.

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

Больше примеров смотрите в документации в разделе [примеры](docs/Examples).

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

### Отправка сообщения

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$client = new MaxApiClient('your-access-token');

$client->sendMessageToUser(
    12345678,
    NewMessageBody::new('Privet!')
);
```

### Ответ на сообщение пользователя

TODO:

## Документация

- [Начало работы](docs/GettingStarted.md)
- [Список документации](docs/DocumentationList.md)
