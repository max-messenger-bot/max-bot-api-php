# Max Bot & Max API Client For PHP

> [!NOTE]
> Это неофициальные MAX API клиент и бот.
> Проект находится на стадии тестирования, все документированные в официальной документации возможности реализованы.

> [!WARNING]
> По поводу ошибок в клиенте, пожалуйста обращайтесь ко мне напрямую:
>   - Max: [Евгений](https://max.ru/u/f9LHodD0cOID7ezkLpMv_5wNX9YmRCmk-0bp4q1uWCRtrdClF9F21Buxhyk)
>   - Telegram: [mj4444ru](https://t.me/mj4444ru)

> [!NOTE]
> Вы можете заметить некоторые отличия текущей реализации от официальной документации.
> На самом деле, официальная документация может содержать неточности или иметь дублирующиеся способы получения данных.

> [!NOTE]
> Некоторые недокументированные в официальном API функции могут быть отключены на стороне Max,
> но когда они писались и тестировались, они работали.

## Основные особенности

- Это полностью объектно-ориентированный клиент.
- Для работы с клиентом не требуется изучение официального API.
- В большинстве случаев для понимания работы, Вам достаточно будет посмотреть [примеры кода](./docs/Examples/README.md).
- Есть валидация данных в моделях запросов (можно отключить).
- Реализована загрузка файлов на сервера обоими поддерживаемыми способами.
- Имеются утилиты (скрипты) для тестирования и отладки обработки обновлений.
- Весь функционал разбит на слои (бот, API Max клиент, Http клиент для API Max, Curl Http клиент),
  каждый слой может быть частично или полностью заменён Вашей реализацией
  (используются интерфейсы и многие внутренние методы объявлены как публичные).
- Код реализован с возможностью написания тестов для любой части Вашего кода.
    - API Max клиент реализован на основе официальной документации API Max в формате `yaml`.
        - Объектная модель, имена моделей, имена параметров, документирование сохранены.
        - Дополнительно добавлено множество методов, упрощающих работу с API.

## Документация в коде

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

- `mj4444/simple-http-client` ^0.2.0 — HTTP-клиент для выполнения запросов

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
$bot->onBotStarted(function (BotStartedEvent $event): true {
    // Обработка события
    return true;
});

// Добавление обработчика сообщений
$bot->onMessageCreated(function (MessageCreatedEvent $event): true {
    $event->reply("Ваше сообщение:\n" . $event->getMessage()->getText(), true)
    // Обработка нового сообщения
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

$client = new MaxApiClient('your-access-token');

$client->sendMessageToUser(12345678, 'Привет');
```

#### Отправка сообщения с кнопкой

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Requests\LinkButton;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$client = new MaxApiClient('your-access-token');

$message = NewMessageBody::make('Тест')
    ->addInlineKeyboardAttachment([[LinkButton::make('Документация', 'https://dev.max.ru/docs-api')]]);
$client->sendMessageToUser(12345678, $message);
```

#### Отправка сообщения с файлом

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;
use MaxMessenger\Bot\Uploaders\Contents\File;

$client = new MaxApiClient('your-access-token');

$uploader = $apiClient->getSimpleUploader();
$fileToken = $uploader->uploadFile(new File(__FILE__));

// Задержка, чтобы сервера Max обработали файл (нужна только для некоторых типов вложений).
// При её отсутствии, клиент может сделать повторный запрос, если вложение не готово.
sleep(1);

$message = NewMessageBody::new()
    ->addFileAttachment($fileToken);
$client->sendMessageToUser(12345678, $message);
```

#### Загрузка файла частями

```php
use MaxMessenger\Bot\Uploaders\Contents\File;
use MaxMessenger\Bot\Uploaders\FragmentUploadStat;

$uploader = $apiClient->getUploader();
$uploader->setProgressCallback(static function (string $postName, FragmentUploadStat $stat): void {
    echo sprintf(
        "=== %s === frag-offset: %d, frag-length: %d, file-size: %d, time: %s\n",
        $postName,
        $stat->offset,
        $stat->length,
        $stat->size,
        number_format($stat->time, 2)
    );
});

$fileToken = $uploader->uploadFile(new File(__FILE__, 'Демо.txt'));
```

## Документация

- [Начало работы](docs/GettingStarted.md)
- [Список документации](docs/README.md)
