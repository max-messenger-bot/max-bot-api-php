# Max Bot & Max API Client For PHP

<img src="docs/images/bot-icon.webp" align="left">
Этот пакет предназначен для работы с Max API в полностью объектно-ориентированном формате. Все запросы, ответы и
обновления сервера представлены в строго типизированном объектном виде, никаких array shapes (object-like arrays).

Весь задокументированный функционал реализован, включая возможность возобновления загрузки файлов.

**Актуальность:** 5 апреля 2026 г.

```php
use MaxMessenger\Bot\MaxApiClient;

$apiClient = new MaxApiClient('your-access-token');

$apiClient->sendMessageToUser(12345678, 'Привет');
```

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret');

$bot->onBotStarted(function (BotStartedEvent $event): void {
    $event->sendToChat(sprintf('Здравствуйте, %s!', $event->getUser()->getFirstName());
});

$bot->onMessageCreated(function (MessageCreatedEvent $event): void {
    $message = $event->getMessage()->getText();
    // Обработка сообщения
    $event->reply('Ваше сообщение получено.', true)
});

$bot->handleFromGlobal();
```

> [!WARNING]
> По поводу ошибок в клиенте, пожалуйста обращайтесь ко мне напрямую:
>   - Max: [Евгений](https://max.ru/u/f9LHodD0cOID7ezkLpMv_5wNX9YmRCmk-0bp4q1uWCRtrdClF9F21Buxhyk)
>   - Telegram: [mj4444ru](https://t.me/mj4444ru)

> [!NOTE]
> Вы можете заметить некоторые отличия реализации от официальной документации.
> На самом деле, официальная документация может содержать неточности или иметь дублирующиеся способы получения данных.

Некоторые недокументированные в официальном API функции могут быть отключены на стороне Max.
Когда они писались и тестировались, они работали.

## Если вы думаете, что этот пакет слишком сложный

1. Если Вам нужно просто отправить сообщение, то никакие пакеты Вам не нужны, просто прочитайте главу
   **Отправка сообщений используя curl** в разделе [Отправка сообщений](docs/Examples/SendingMessages.md).
2. Если Вам нужно что-то ещё, прочитайте раздел [Очень простой бот](docs/VerySimpleBot.md).

## Основные особенности

- Это полностью объектно-ориентированный клиент без array shapes (object-like arrays).
- Для работы с клиентом не требуется изучение официального API.
- В большинстве случаев для понимания работы, Вам достаточно будет посмотреть [примеры кода](./docs/Examples/README.md).
- Есть валидация данных в моделях запросов (можно отключить).
- Реализована загрузка файлов на сервера обоими поддерживаемыми способами.
- Имеются утилиты (скрипты) для тестирования и отладки обработки обновлений.
- Весь функционал разбит на слои (бот, API Max клиент, HTTP клиент для API Max, Curl HTTP клиент),
  каждый слой может быть частично или полностью заменён Вашей реализацией
  (используются интерфейсы и многие внутренние методы объявлены как публичные).
- Код реализован с возможностью написания тестов для любой части Вашего кода.
- API Max клиент реализован на основе официальной документации API Max в формате `yaml`.
    - Объектная модель, имена моделей, имена параметров сохранены. Документирование откорректировано и дополнено.
    - Дополнительно добавлено множество методов, упрощающих работу с API.

## Документация в коде

I believe that in-code documentation should be in English. However, due to a lack of resources to translate
the documentation into English, the in-code documentation is presented in Russian.

Я считаю, что документация в публичном коде должна быть на английском языке. Однако из-за нехватки ресурсов для перевода
документации на английский язык, документация в коде представлена на русском языке.

## Установка

```bash
composer require max-messenger-bot/max-bot-api-php
```

### Требования

- PHP 8.2+
- Расширение `ext-mbstring`

### Зависимости

- `mj4444/simple-http-client` ^0.2.1 — HTTP-клиент для выполнения запросов

## Примеры

Больше примеров смотрите в документации в разделе [примеры](docs/Examples/README.md).

### Обработка обновлений через Webhook (основной метод)

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret');

// Добавление обработчика команды
$bot->getCommandHandler()
    ->onCommand('start', function (MessageCreatedEvent $event): bool {
        // Обработка команды /start
        return true;
    });

// Добавление обработчика присоединения нового пользователя
$bot->onBotStarted(function (BotStartedEvent $event): bool {
    // Обработка события
    return true;
});

// Добавление обработчика сообщений
$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    // Обработка нового сообщения
    $event->reply('Ваше сообщение получено', true)
    return true;
});

$apiClient->handleFromGlobal();
```

### Обработка обновлений через Long Polling

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token', 'your-secret');

// Добавление обработчиков

// Запуск обработки обновлений с сервера
$marker = null;
while (true) {
    $marker = $bot->handleFromServer(marker: $marker);
    usleep(100000);
}
```

### Отправка сообщений

#### Отправка простого сообщения

```php
use MaxMessenger\Bot\MaxApiClient;

$apiClient = new MaxApiClient('your-access-token');

$apiClient->sendMessageToUser(12345678, 'Привет');
```

#### Отправка сообщения с кнопкой

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$apiClient = new MaxApiClient('your-access-token');

$message = NewMessageBody::make('Сообщение с клавиатурой');
$message->addInlineKeyboard()->addLinkButton('Документация', 'https://dev.max.ru/docs-api');
$apiClient->sendMessageToUser(12345678, $message);
```

#### Отправка сообщения с файлом

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$apiClient = new MaxApiClient('your-access-token');

$message = NewMessageBody::new()
    ->addFileAttachment($fileToken);
$apiClient->sendMessageToUser(12345678, $message);
```

#### Загрузка файлов на сервера Max

Для загрузки файлов на сервера Max установите и используйте пакет
[max-messenger-bot/max-uploader-php](https://github.com/max-messenger-bot/max-uploader-php).

```bash
composer require max-messenger-bot/max-uploader-php
```

## Документация

- [Начало работы](docs/GettingStarted.md)
- [Список документации](docs/README.md)
