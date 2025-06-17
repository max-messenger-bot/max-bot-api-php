# Отправка сообщений

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
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$apiClient->sendMessageToUser($userId, NewMessageBody::make('Привет'));
```

### Отправка простого текстового сообщения в чат или диалог

**Диалог** — это частный случай чата, в котором есть только бот и пользователь.
Когда пользователь пишет боту, он пишет в диалог. Нет разницы писать напрямую пользователю или в диалог.
Тесты показали, что идентификатор диалога не меняется при заморозке и удалении бота.

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$apiClient->sendMessageToChat($chatId, 'Привет');
```

## Отправка сообщений с форматированием

```php
use MaxMessenger\Bot\Models\Enums\TextFormat;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText("**Жирный**\n*курсив*\n++подчёркнутый++\n~~зачёркнутый~~\n`моноширинный`")
    ->setFormat(TextFormat::Markdown);
$apiClient->sendMessageToUser($userId, $message);

$message = NewMessageBody::new()
    ->setText("```\nМоноширинный\nМногострочный\n```")
    ->setFormat(TextFormat::Markdown);
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
$chatId = $event->getMessage()->getRecipient()->getChatId();
$event->apiClient->sendMessageToChat($chatId, 'Привет');
```

## Реакции на события бота

У разных событий есть разные методы реакций на события.

- `$event->sendToChat($message, $disableLinkPreview = false)`,
  `$event->sendToUser($message, $disableLinkPreview = false)`
    - **BotAddedToChatEvent**
    - **BotRemovedFromChatEvent**
    - **BotStartedEvent**
    - **ChatTitleChangedEvent**
    - **DialogClearedEvent**
    - **DialogMutedEvent**
    - **DialogUnmutedUpdate**
    - **MessageCallbackEvent**
    - **MessageRemovedEvent**
    - **UserAddedToChatEvent**
    - **UserRemovedFromChatEvent**
- `$event->forwardToChat($chatId)`, `$event->forwardToUser($userId)`,
  `$event->reply($message, $asReply = false, $disableLinkPreview = false)`,
  `$event->replyToUser($message, $forwardOrigMessage = false, $disableLinkPreview = false)`
    - **MessageCreatedEvent**
    - **MessageEditedEvent**
- `$event->answer($message, $notification = null)`, `$event->answerNotification($notification)`
    - **MessageCallbackEvent**
- `$event->deleteMessage($mid = null);`
    - **MessageCallbackEvent**
    - **MessageCreatedEvent**
    - **MessageEditedEvent**

## Отправка сообщений используя curl

**Отправка сообщения пользователю:**

```php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://platform-api.max.ru/messages?user_id=' . $userId);
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
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
```

**Отправка сообщения в чат или диалог:**

```php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://platform-api.max.ru/messages?chat_id=' . $chatId);
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
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
```
