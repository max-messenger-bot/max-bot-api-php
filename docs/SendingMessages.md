# Отправка сообщений

Документация описывает способы отправки сообщений через Max Messenger Bot API.

## Содержание

- [Основные методы](#основные-методы)
    - [sendMessageToChat](#sendmessagetochat)
    - [sendMessageToUser](#sendmessagetouser)
    - [sendMessage](#sendmessage)
- [Создание сообщения](#создание-сообщения)
    - [Простое текстовое сообщение](#простое-текстовое-сообщение)
    - [Через NewMessageBody](#через-newmessagebody)
- [Форматирование текста](#форматирование-текста)
- [Ссылки в сообщениях](#ссылки-в-сообщениях)
    - [Ответ на сообщение](#ответ-на-сообщение)
    - [Пересылка сообщения](#пересылка-сообщения)
- [Вложения](#вложения)
    - [Изображения](#изображения)
    - [Видео](#видео)
    - [Аудио](#аудио)
    - [Файлы](#файлы)
    - [Стикеры](#стикеры)
    - [Геолокация](#геолокация)
    - [Контакт](#контакт)
    - [Предпросмотр ссылки](#предпросмотр-ссылки)
- [Клавиатуры](#клавиатуры)
    - [Inline-клавиатура](#inline-клавиатура)
    - [Reply-клавиатура](#reply-клавиатура)
    - [Типы кнопок](#типы-кнопок)
- [Отправка из обработчиков событий](#отправка-из-обработчиков-событий)
    - [Методы reply](#методы-reply)
    - [Методы sendToChat / sendToUser](#методы-sendtochat--sendtouser)
    - [Методы forwardToChat / forwardToUser](#методы-forwardtochat--forwardtouser)
- [Уведомления и оповещения](#уведомления-и-оповещения)
- [Обработка ошибок](#обработка-ошибок)
- [Ответ метода](#ответ-метода)

## Основные методы

### sendMessageToChat

Отправляет сообщение в чат по его ID:

```php
use MaxMessenger\Bot\MaxApiClient;

$client = new MaxApiClient('your-access-token');

$result = $client->sendMessageToChat($chatId, 'Привет, чат!');
```

Параметры:

- `$chatId` (int) — ID чата
- `$message` (NewMessageBody|RawModel|string) — тело сообщения
- `$disableLinkPreview` (bool) — отключение предпросмотра ссылок (по умолчанию `false`)

### sendMessageToUser

Отправляет сообщение пользователю по его ID:

```php
$result = $client->sendMessageToUser($userId, 'Привет!');
```

Параметры:

- `$userId` (int) — ID пользователя
- `$message` (NewMessageBody|RawModel|string) — тело сообщения
- `$disableLinkPreview` (bool) — отключение предпросмотра ссылок (по умолчанию `false`)

### sendMessage

Универсальный метод. Требует указания либо `$userId`, либо `$chatId` (но не обоих одновременно):

```php
// Отправка пользователю
$result = $client->sendMessage(userId: $userId, chatId: null, message: 'Привет!');

// Отправка в чат
$result = $client->sendMessage(userId: null, chatId: $chatId, message: 'Привет, чат!');
```

Параметры:

- `$userId` (int|null) — ID пользователя
- `$chatId` (int|null) — ID чата
- `$message` (NewMessageBody|RawModel|string) — тело сообщения
- `$disableLinkPreview` (bool) — отключение предпросмотра ссылок (по умолчанию `false`)

> Один из параметров `$userId` или `$chatId` должен быть не-null. Оба одновременно — ошибка.

## Создание сообщения

### Простое текстовое сообщение

Сообщение можно передать как строку:

```php
$client->sendMessageToUser($userId, 'Привет!');
```

Поддерживается перенос строк:

```php
$client->sendMessageToUser($userId, "Первая строка\nВторая строка");
```

### Через NewMessageBody

Для более сложных сценариев используйте класс `NewMessageBody`:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::make('Привет!');
$client->sendMessageToUser($userId, $message);
```

Или через fluent-интерфейс:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText('Привет!')
    ->setNotify(false); // Без уведомления
    
$client->sendMessageToUser($userId, $message);
```

> **`make()`** требует указания обязательных параметров в конструкторе.<br>
> **`new()`** позволяет создавать объект без обязательных параметров и задавать их позже.

## Форматирование текста

Поддерживается markdown-форматирование:

```php
use MaxMessenger\Bot\Models\Enums\TextFormat;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText("**Жирный**\n*курсив*\n++подчёркнутый++\n~~зачёркнутый~~\n`моноширинный`")
    ->setFormat(TextFormat::Markdown);

$client->sendMessageToUser($userId, $message);
```

Многострочный моноширинный блок:

```php
use MaxMessenger\Bot\Models\Enums\TextFormat;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::make(
    "```\nМногострочный\nкод\n```",
    format: TextFormat::Markdown
);
$client->sendMessageToUser($userId, $message);
```

Ссылки в markdown:

```php
use MaxMessenger\Bot\Models\Enums\TextFormat;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText("[Документация](https://dev.max.ru/docs-api)\n[Пользователь](max://user/12345678)")
    ->setFormat(TextFormat::Markdown);
```

> Для ссылки на пользователя необходимо указать реальное имя как в профиле, иначе ссылка не отобразится.

Доступные форматы (`TextFormat`):

- `TextFormat::Markdown` — markdown-форматирование

## Ссылки в сообщениях

### Ответ на сообщение

Чтобы ответить на существующее сообщение, используйте `setReplyLink()`:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText('Это ответ')
    ->setReplyLink($messageId); // mid, например 'mid.abc123'

$client->sendMessageToUser($userId, $message);
```

Можно передать объект `Message` или `MessageBody`:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText('Это ответ')
    ->setReplyLink($origMessage); // объект Message или MessageBody
```

### Пересылка сообщения

Для пересылки используйте `setForwardLink()`:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText('Пересланное сообщение')
    ->setForwardLink($messageId);

$client->sendMessageToChat($chatId, $message);
```

## Вложения

> Для загрузки файлов на сервера и получения токена вложения, используйте
> пакет [max-messenger-bot/max-uploader-php](https://github.com/max-messenger-bot/max-uploader-php).

`NewMessageBody` поддерживает различные типы вложений через методы `add*Attachment()`.

### Изображения

**Из внешнего URL:**

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText('Смотри!')
    ->addUrlImageAttachment('https://example.com/image.jpg');
```

**Из ранее загруженного вложения (по токену):**

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->addImageAttachment($imageToken);
```

### Видео

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->addVideoAttachment($videoToken);
```

### Аудио

Аудио должно быть единственным вложением:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->addAudioAttachment($audioToken);
```

### Файлы

Файл должен быть единственным вложением:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->addFileAttachment($fileToken);
```

### Стикеры

Стикер должен быть единственным вложением:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->addStickerAttachment($stickerCode);
```

### Геолокация

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->addLocationAttachment(55.7558, 37.6173); // широта, долгота
```

### Контакт

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

// По ID контакта
$message = NewMessageBody::new()
    ->addContactAttachment(contactId: 12345);

// С vCard-данными
$message = NewMessageBody::new()
    ->addContactAttachment(vcfInfo: 'BEGIN:VCARD...');
```

### Предпросмотр ссылки

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->addShareAttachment('https://example.com');
```

## Клавиатуры

### Inline-клавиатура

Inline-клавиатура отображается под сообщением. Поддерживается до 210 кнопок в 30 рядах (до 7 кнопок в ряду).

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText('Выберите действие:');

$keyboard = $message->addInlineKeyboard();

// Добавление кнопок
$keyboard
    ->addCallbackButton('Да', 'action_yes')
    ->addCallbackButton('Нет', 'action_no')
    ->newRow()
    ->addLinkButton('Подробнее', 'https://example.com');

$client->sendMessageToUser($userId, $message);
```

Или с готовым массивом кнопок:

```php
use MaxMessenger\Bot\Models\Requests\CallbackButton;
use MaxMessenger\Bot\Models\Requests\LinkButton;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$keyboard = [
    [
        CallbackButton::make('Да', 'action_yes'),
        CallbackButton::make('Нет', 'action_no'),
    ],
    [
        LinkButton::make('Подробнее', 'https://example.com'),
    ],
];

$message = NewMessageBody::new()
    ->setText('Выберите:')
    ->addInlineKeyboardAttachment($keyboard);

$client->sendMessageToUser($userId, $message);
```

### Типы кнопок

| Класс                      | Где использовать | Описание                                            |
|----------------------------|------------------|-----------------------------------------------------|
| `CallbackButton`           | Inline           | Отправляет callback боту с payload                  |
| `LinkButton`               | Inline           | Открывает URL                                       |
| `MessageButton`            | Inline           | Отправляет текст кнопки в чат от имени пользователя |
| `ChatButton`               | Inline           | Создаёт новый чат                                   |
| `ClipboardButton`          | Inline           | Копирует текст в буфер обмена                       |
| `OpenAppButton`            | Inline           | Запускает мини-приложение                           |
| `RequestContactButton`     | Inline           | Запрашивает контакт пользователя                    |
| `RequestGeoLocationButton` | Inline           | Запрашивает геолокацию                              |
| `SendMessageButton`        | Reply            | Отправляет сообщение с заданным payload             |
| `SendContactButton`        | Reply            | Отправляет контакт                                  |
| `SendGeoLocationButton`    | Reply            | Отправляет геолокацию                               |

Пример создания чата через кнопку:

```php
$keyboard->addChatButton(
    text: 'Создать чат',
    chatTitle: 'Новый чат',
    chatDescription: 'Описание чата',
    startPayload: 'start_command'
);
```

Пример запуска мини-приложения:

```php
$keyboard->addOpenAppButton(
    text: 'Открыть приложение',
    webApp: 'my_bot_username',
    payload: 'param=value'
);
```

## Отправка из обработчиков событий

### Методы reply

События `MessageCreatedEvent` и `MessageEditedEvent` имеют метод `reply()`:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$bot->onMessageCreated(function (MessageCreatedEvent $event) {
    // Простой ответ
    $event->reply('Сообщение получено');
    
    // Ответ с цитатой оригинального сообщения
    $event->reply('Ответ с цитатой', asReply: true);
});
```

Для событий с callback (`MessageCallbackEvent`) доступен метод `answer()`:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$bot->onMessageCallback(function (MessageCallbackEvent $event) {
    // Ответ с обновлённым сообщением и/или уведомлением
    $event->answer('Текст ответа');
    
    // Только уведомление
    $event->answerNotification('Всплывающее уведомление');
});
```

### Методы sendToChat / sendToUser

Доступны в событиях, где можно отправить сообщение:

```php
// Ответ пользователю
$event->sendToUser('Привет!');

// Ответ в чат
$event->sendToChat('Всем привет!');

// Отключение превью ссылок
$event->sendToUser('https://example.com', disableLinkPreview: true);
```

### Методы forwardToChat / forwardToUser

События `MessageCreatedEvent` и `MessageEditedEvent` позволяют переслать сообщение:

```php
// Переслать в другой чат
$event->forwardToChat($targetChatId);

// Переслать пользователю
$event->forwardToUser($targetUserId);
```

## Уведомления и оповещения

### Без уведомления

По умолчанию сообщение отправляет уведомление участникам. Для отправки без уведомления:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = NewMessageBody::new()
    ->setText('Тихое сообщение')
    ->setNotify(false);
```

### Действия бота

Перед отправкой можно показать действие «бот печатает»:

```php
use MaxMessenger\Bot\Models\Enums\SenderAction;

$client->sendAction($chatId, SenderAction::TypingOn);
$client->sendAction($chatId, SenderAction::SendingPhoto);
$client->sendAction($chatId, SenderAction::SendingVideo);
$client->sendAction($chatId, SenderAction::SendingAudio);
$client->sendAction($chatId, SenderAction::SendingFile);
```

**Пометка сообщений как прочитанных:**

```php
use MaxMessenger\Bot\Models\Enums\SenderAction;

$client->sendAction($chatId, SenderAction::MarkSeen);
```

Действие `MarkSeen` полезно при обработке входящих сообщений, чтобы показать пользователю,
что бот прочитал его сообщение:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;
use MaxMessenger\Bot\Models\Enums\SenderAction;

$bot->onMessageCreated(function (MessageCreatedEvent $event) {
    // Помечаем сообщение как прочитанное
    $event->sendAction(SenderAction::MarkSeen);
    
    // Затем отвечаем
    $event->reply('Сообщение прочитано');
});
```

Или через объект:

```php
use MaxMessenger\Bot\Models\Enums\SenderAction;
use MaxMessenger\Bot\Models\Requests\ActionRequestBody;

$client->sendAction($chatId, new ActionRequestBody(SenderAction::TypingOn));
$client->sendAction($chatId, new ActionRequestBody(SenderAction::MarkSeen));
```

## Обработка ошибок

Метод `sendMessage()` автоматически повторяет запрос при ошибке неготовности вложения (`attachment.not.ready`):

```php
// Настройка повторных попыток (задержки в миллисекундах)
$client->retryAttempts = [1000, 2000, 4000, 8000, 15000];
```

Для отключения повторных попыток:

```php
$client->retryAttempts = [];
```

Значение по умолчанию берётся из объекта конфигурации (`MaxApiConfigInterface::getRetryAttempts()`).

При других ошибках выбрасываются исключения:

- `BadRequestException` — неверный запрос (400)
- `UnauthorizedException` — неавторизован (401)
- `ForbiddenException` — доступ запрещён (403)
- `NotFoundException` — ресурс не найден (404)
- `TooManyRequestsException` — слишком много запросов (429)

## Ответ метода

Методы отправки возвращают объект `SendMessageResult`, содержащий созданное сообщение:

```php
$result = $client->sendMessageToUser($userId, 'Привет!');

// Получение информации о сообщении
$message = $result->getMessage();

// ID сообщения
$messageId = $message->getBody()->getMid();

// ID чата
$chatId = $message->getRecipient()->getChatId();
```

`SendMessageResult::getMessage()` возвращает объект `Message` со всей информацией о созданном сообщении.
