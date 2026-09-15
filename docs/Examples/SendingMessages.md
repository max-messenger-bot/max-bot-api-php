# Примеры отправки сообщений

В данном разделе собраны основные способы отправки сообщений без вложений. Больше информации об отправке сообщений,
в том числе с вложениями читайте в разделе [Отправка сообщений](../SendingMessages.md).

## Отправка сообщений используя API-клиента

### Отправка простого текстового сообщения пользователю

```php
$apiClient->sendMessageToUser($userId, 'Привет');
```

**Отправка сообщения со ссылкой:**

```php
$apiClient->sendMessageToUser($userId, 'https://dev.max.ru/docs-api');
```

**Отправка сообщения со ссылкой, но без автоматической генерации preview:**

```php
$apiClient->sendMessageToUser($userId, 'https://dev.max.ru/docs-api', true);
```

**Две строки текста:**

```php
$apiClient->sendMessageToUser($userId, "Привет\nHello");
```

**Через объект класса NewMessageBody:**

```php
use MaxMessenger\Bot\Model\Request\NewMessageBody;

$apiClient->sendMessageToUser($userId, NewMessageBody::make('Привет'));
```

> Метод `make()` отличается от метода `new()` тем, что требует указания обязательных для модели параметров.

### Отправка простого текстового сообщения в чат или диалог

**Диалог** — это частный случай чата, в котором есть только бот и пользователь.
Когда пользователь пишет боту, он пишет в диалог. Нет разницы писать напрямую пользователю или в диалог.
Тесты показали, что идентификатор диалога не меняется при заморозке и удалении бота.

```php
$apiClient->sendMessageToChat($chatId, 'Привет');
```

## Отправка сообщений с форматированием

```php
use MaxMessenger\Bot\Model\Enum\TextFormat;
use MaxMessenger\Bot\Model\Request\NewMessageBody;

$message = NewMessageBody::new()
    ->setText("**Жирный**\n*курсив*\n++подчёркнутый++\n~~зачёркнутый~~\n`моноширинный`")
    ->setFormat(TextFormat::Markdown);
$apiClient->sendMessageToUser($userId, $message);

$message = NewMessageBody::make("```\nМоноширинный\nМногострочный\n```", format: TextFormat::Markdown);
$apiClient->sendMessageToUser($userId, $message);

$message = NewMessageBody::new()
    ->setText("[Документация](https://dev.max.ru/docs-api)\n[Имя](max://user/12345678)")
    ->setFormat(TextFormat::Markdown);
$apiClient->sendMessageToUser($userId, $message);
```

> Для отправки ссылки на пользователя, необходимо указать реальное имя как в профиле, иначе ссылки не будет.

## Отправка сообщений используя событие бота

**Простой ответ:**

```php
$event->reply('Сообщение получено');
```

**Ответ с цитатой сообщения:**

```php
$event->reply('Сообщение получено', true);
```

**Ответ используя API клиент:**

```php
$event->apiClient->sendMessageToChat($event->getChatId(), 'Привет');
```

> Если событие относится к объекту, который не поддерживается MAX API, сообщения в нём нет
> и `getChatId()` прерывает обработку события через `BaseEvent::continue()`.

## Реакции на события бота

У разных событий есть разные методы реакций на события.

- `$event->sendMessageToChat($message, $disableLinkPreview = false)`
    - **BotAddedToChatEvent**
    - **BotStartedEvent**
    - **ChatTitleChangedEvent**
    - **DialogClearedEvent**
    - **DialogMutedEvent**
    - **DialogUnmutedEvent**
    - **MessageCallbackEvent**
    - **MessageCreatedEvent**
    - **MessageEditedEvent**
    - **MessageRemovedEvent**
    - **UserAddedToChatEvent**
    - **UserRemovedFromChatEvent**
- `$event->sendMessageToUser($message, $disableLinkPreview = false)`
    - все события из списка выше
    - **BotRemovedFromChatEvent**
    - **CommentCreatedEvent**
    - **CommentEditedEvent**
    - **CommentRemovedEvent**
- `$event->forwardToChat($chatId)`, `$event->forwardToUser($userId)`,
  `$event->reply($message, $asReply = false, $disableLinkPreview = false)`,
  `$event->replyToUser($message, $forwardOrigMessage = false, $disableLinkPreview = false)`
    - **MessageCallbackEvent**
    - **MessageCreatedEvent**
    - **MessageEditedEvent**
- `$event->answer($message)`
    - **MessageCallbackEvent**
- `$event->deleteMessage();`
    - **MessageCallbackEvent**
    - **MessageCreatedEvent**
    - **MessageEditedEvent**

## Отправка сообщений используя curl

**Отправка сообщения пользователю:**

```php
// Подготовка curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

// Формирование curl запроса
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_URL, 'https://platform-api2.max.ru/messages?user_id=' . $userId);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'text' => 'Это сообщение с кнопкой-ссылкой',
    'attachments' => [[
        'type' => 'inline_keyboard',
        'payload' => [
            'buttons' => [[
                [
                    'type' => 'link',
                    'text' => 'Откройте сайт',
                    'url' => 'https://example.com'
                ],
            ]],
        ],
    ]],
]));

// Выполнение запроса
$response = curl_exec($ch);

// Проверка результата
if ($response === false) {
    curl_close($ch);
    throw new RuntimeException(curl_error($ch), curl_errno($ch));
}
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($httpCode !== 200 || !str_contains($response, '"mid":"')) {
    throw new RuntimeException('The message was not sent: ' . $response);
}
```

**Отправка сообщения в чат или диалог:**

Замените:

```php
curl_setopt($ch, CURLOPT_URL, 'https://platform-api2.max.ru/messages?user_id=' . $userId);
```

на:

```php
curl_setopt($ch, CURLOPT_URL, 'https://platform-api2.max.ru/messages?chat_id=' . $chatId);
```
