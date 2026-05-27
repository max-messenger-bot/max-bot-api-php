# Примеры отправки действий

## Введение

Действия бота показывают пользователям, что бот делает в чате. Это улучшает пользовательский опыт, показывая индикаторы
активности.

**Связанная документация:**

- [API-клиент](../ApiClient.md) — метод `sendAction()`
- [Примеры обработки событий](ProcessingEvents.md)

## Доступные действия

Перечисление `SenderAction` определяет следующие действия:

| Действие       | Значение        | Описание                               |
|----------------|-----------------|----------------------------------------|
| `TypingOn`     | `typing_on`     | Бот набирает сообщение                 |
| `SendingPhoto` | `sending_photo` | Бот отправляет фото                    |
| `SendingVideo` | `sending_video` | Бот отправляет видео                   |
| `SendingAudio` | `sending_audio` | Бот отправляет аудиофайл               |
| `SendingFile`  | `sending_file`  | Бот отправляет файл                    |
| `MarkSeen`     | `mark_seen`     | Бот помечает сообщения как прочитанные |

## Отправка действий через API-клиент

### Отправка действия набора текста

Простой пример отправки индикатора "печатает":

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Enum\SenderAction;

$client = new MaxApiClient('your-access-token');

// Отправляем действие "печатает" в чат
$client->sendAction($chatId, SenderAction::TypingOn);
```

### Отправка действия с использованием RawModel

Вы также можете использовать `RawModel` для отправки действий:

```php
use MaxMessenger\Bot\Model\Request\RawModel;

$action = new RawModel(['action' => 'typing_on']);

$client->sendAction($chatId, $action);
```

## Практические примеры

### Показ действия перед отправкой сообщения

Показываем пользователю, что бот正在准备 ответ:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Enum\SenderAction;

$bot = new MaxBot('your-access-token', 'your-secret');

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $chatId = $event->getChatId();
    $text = $event->getMessage()->getBody()->getText();
    
    // Показываем, что бот печатает
    $event->apiClient->sendAction($chatId, SenderAction::TypingOn);
    
    // Имитация задержки обработки
    sleep(1);
    
    // Отправляем ответ
    $event->reply('Вы написали: ' . $text);

    return true; // Отмечаем событие как обработанное
});
```

### Цепочка действий при обработке

Последовательная смена действий для реалистичного поведения:

```php
use MaxMessenger\Bot\Model\Enum\SenderAction;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $chatId = $event->getChatId();
    $apiClient = $event->apiClient;
    
    // Сначала показываем, что печатаем
    $apiClient->sendAction($chatId, SenderAction::TypingOn);
    sleep(1);
    
    // Потом показываем, что отправляем фото
    $apiClient->sendAction($chatId, SenderAction::SendingPhoto);
    sleep(1);
    
    // Отправляем сообщение с фото
    // ...

    return true; // Отмечаем событие как обработанное
});
```

### Отметка сообщений как прочитанных

Используйте действие `mark_seen` для отметки сообщений:

```php
use MaxMessenger\Bot\Model\Enum\SenderAction;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $chatId = $event->getChatId();
    
    // Отмечаем сообщения как прочитанные
    $event->apiClient->sendAction($chatId, SenderAction::MarkSeen);
    
    // Обрабатываем сообщение...

    return true; // Отмечаем событие как обработанное
});
```

### Действие при загрузке файлов

Показываем пользователю, что бот отправляет файл:

```php
use MaxMessenger\Bot\Model\Enum\SenderAction;
use MaxMessenger\Bot\Model\Request\NewMessageBody;

$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $chatId = $event->getChatId();
    $apiClient = $event->apiClient;
    
    // Показываем, что отправляем файл
    $apiClient->sendAction($chatId, SenderAction::SendingFile);
    
    // Загрузка и отправка файла...
    // $apiClient->sendMessage(...);

    return true; // Отмечаем событие как обработанное
});
```

## Полный пример бота с действиями

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\BaseEvent;
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;
use MaxMessenger\Bot\Model\Enum\SenderAction;
use Throwable;

$bot = new MaxBot('your-access-token', 'your-secret');

// Обработка сообщений с индикатором действий
$bot->onMessageCreated(function (MessageCreatedEvent $event): bool {
    $chatId = $event->getChatId();
    $apiClient = $event->apiClient;
    $text = $event->getMessage()->getBody()->getText();
    
    // Показываем, что бот печатает
    $apiClient->sendAction($chatId, SenderAction::TypingOn);
    
    // Имитация обработки
    usleep(500000); // 0.5 секунды
    
    // Отмечаем как прочитанное
    $apiClient->sendAction($chatId, SenderAction::MarkSeen);
    
    // Отправляем ответ
    $event->reply('Получил ваше сообщение: ' . $text);

    return true; // Отмечаем событие как обработанное
});

// Обработка ошибок
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    error_log("Ошибка: " . $exception->getMessage());

    return true; // Отмечаем событие как обработанное
});

// Запуск обработки событий
$marker = null;
while (true) {
    try {
        $marker = $bot->handleFromServer(marker: $marker);
    } catch (Throwable $e) {
        error_log("Критическая ошибка: " . $e->getMessage());
        sleep(5);
    }
    
    usleep(100000); // 100ms между запросами
}
```

## Рекомендации

### Не злоупотребляйте действиями

Не отправляйте слишком много действий подряд, это может создать нагрузку на API:

```php
// ❌ ПЛОХО: Слишком много действий
$apiClient->sendAction($chatId, SenderAction::TypingOn);
$apiClient->sendAction($chatId, SenderAction::TypingOn);
$apiClient->sendAction($chatId, SenderAction::TypingOn);

// ✅ ХОРОШО: Одно действие перед ответом
$apiClient->sendAction($chatId, SenderAction::TypingOn);
sleep(1);
$event->reply('Ответ');
```

### Используйте соответствующие действия

Выбирайте действие, соответствующее типу контента, который вы отправляете:

```php
// Для текста
$apiClient->sendAction($chatId, SenderAction::TypingOn);

// Для фото
$apiClient->sendAction($chatId, SenderAction::SendingPhoto);

// Для видео
$apiClient->sendAction($chatId, SenderAction::SendingVideo);
```

### Действия работают только в групповых чатах

Метод `sendAction()` работает только для групповых чатов (не для диалогов). Убедитесь, что вы отправляете действия в
правильный тип чата.
