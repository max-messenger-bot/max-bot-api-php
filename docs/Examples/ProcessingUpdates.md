# Обработка обновлений

## Рекомендации

- Используйте рекомендации из документации описания процесса [обработка обновлений](../ProcessingUpdates.md).
- Когда Вы настраиваете получение обновлений о действиях в чат-боте, используйте:
    - **Long Polling** — для разработки и тестирования.
    - **Webhook** — для production-окружения.

## Примеры кода

- **Создание бота** — описано в разделе [Инициализация](Initialization.md).
- **Добавление обработчиков** — описано в разделе [Добавление обработчиков](AddingHandlers.md).

### Long Polling

```php
// Создание бота
// Добавление обработчиков

$marker = null;
while (true) {
    $marker = $bot->handleFromServer(marker: $marker);
    echo sprintf('%s: Marker: %s' . PHP_EOL, date('Y-m-d H:i:s'), $marker ?? '[null]');
    usleep(100);
}
```

### Webhook

```php
// Создание бота
// Добавление обработчиков

$bot->handleFromGlobal();
```
