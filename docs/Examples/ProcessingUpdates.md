# Примеры обработки обновлений

## Рекомендации

- Используйте рекомендации из документации описания процесса [обработка обновлений](../ProcessingUpdates.md).
- Когда Вы настраиваете получение событий о действиях в чат-боте, используйте:
    - **Long Polling** — для разработки и тестирования.
    - **Webhook** — для production-окружения.

## Примеры кода

- **Создание бота** — описано в разделе [Инициализация](Initialization.md).
- **Обработка событий** — описано в разделе [Обработка команд и событий](ProcessingEvents.md).

### Long Polling

```php
// Создание бота
// Добавление обработчиков

$marker = null;
while (true) {
    $marker = $bot->handleFromServer(marker: $marker);
    echo sprintf('%s: Marker: %s' . PHP_EOL, date('Y-m-d H:i:s'), $marker ?? '[null]');
    usleep(100000);
}
```

### Webhook

```php
// Создание бота
// Добавление обработчиков

$bot->handleFromGlobal();
```

### Самостоятельная обработка Webhook

Если приложение самостоятельно читает входящий запрос средствами фреймворка, проверьте его заголовки и тело
перед созданием обновления. Пример для PSR-7 запроса в `$request`:

```php
use MaxMessenger\Bot\MaxBot;

// Создание бота с секретом Webhook
// Добавление обработчиков

$contentLength = $request->getHeaderLine(MaxBot::CONTENT_LENGTH_HEADER);
$bot->validateRequestHeaders(
    $request->getMethod(),
    $request->getHeaderLine(MaxBot::CONTENT_TYPE_HEADER),
    $contentLength,
    $request->getHeaderLine(MaxBot::SECRET_HEADER),
);

$body = (string) $request->getBody();
MaxBot::validateRequestBody($body, $contentLength);

$update = MaxBot::makeUpdateFromString($body);
$bot->handleUpdate($update);
```

Встроенные `handleFromGlobal()`, `handleFromRequest()`, `readRequestContentFromGlobal()` и `readRequestContent()`
выполняют эти проверки автоматически.

Подробнее: [Безопасность Webhook-запросов](../ProcessingUpdates.md#безопасность-webhook-запросов).
