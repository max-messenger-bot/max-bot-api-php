# Очень простой бот

Вы можете подумать, что написать бота можно проще.\
Это правда, но даже здесь этот пакет может оказать большую помощь.

В любом случае вам потребуется выполнить следующие шаги:

- Реализовать код получения и проверки запроса с «обновлением».
- Реализовать код декодирования текста JSON-запроса.
- Изучить документацию по API Max и углубиться в детали запросов и ответов в формате JSON.
- Реализовать код http-клиента для выполнения запросов к API Max.
- Изучить процедуру регистрации обработчиков запросов с «обновлением».

Вы можете упростить себе эти задачи, используя бота.

**Пример использования бота для получения запроса из глобального контекста и отправки запроса в API Max:**

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token', 'your-secret');

// Проверяем запрос и читаем содержимое запроса из глобального контекста
$body = $bot->readRequestContentFromGlobal();

// Декодируем JSON-запрос
$update = json_decode($body, true, 16, JSON_THROW_ON_ERROR);

// Проверяем тип обновления
if ($update['update_type'] === 'message_created') {
    // Пишем ответ
    $chatId = $update['message']['recipient']['chat_id'];
    $mid = $update['message']['body']['mid'];
    $message = [
        'text' => 'Ваше сообщение получено',
        'link' => [
            'type' => 'reply',
            'mid' => $mid,
        ]
    ];
    $bot->apiClient->getHttpClient()->post('/messages', (object)$message, ['chat_id' => $chatId]);
}
```

**Та же функциональность с использованием обновления бота:**

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;
use MaxMessenger\Bot\Models\Responses\MessageCreatedUpdate;

$bot = new MaxBot('your-access-token', 'your-secret');

// Проверяем запрос и читаем содержимое запроса из глобального контекста
$body = $bot->readRequestContentFromGlobal();

// Декодируем JSON-запрос и получаем из него Update
$update = MaxBot::makeUpdateFromString($body);

// Проверяем тип обновления
if ($update instanceof MessageCreatedUpdate) {
    // Пишем ответ
    $chatId = $update->getMessage()->getRecipient()->getChatId();
    $mid = $update->getMessage()->getBody()->getMid();
    $message = NewMessageBody::make('Ваше сообщение получено')->setReplyLink($mid);
    $bot->apiClient->sendMessageToChat($chatId, $message);
}
```

**Та же функциональность с использованием событий бота:**

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret');

// Проверяем запрос и читаем содержимое запроса из глобального контекста
$body = $bot->readRequestContentFromGlobal();

// Декодируем JSON-запрос и получаем из него Event
$event = $bot->makeEvent(MaxBot::makeUpdateFromString($body));

// Проверяем тип события
if ($event instanceof MessageCreatedEvent) {
    // Пишем ответ
    $event->reply('Ваше сообщение получено', true);
}
```

**Регистрация обработчика используя php:**

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Models\Requests\SubscriptionRequestBody;

$apiClient = new MaxApiClient('your-access-token');

$apiClient->subscribe(SubscriptionRequestBody::make($url, 'your-secret'));
```

**Регистрация обработчика используя скрипт:**

Просто запустите `bin/max-subscribe` или `php bin/max-subscribe.php` и отвечайте на вопросы.
