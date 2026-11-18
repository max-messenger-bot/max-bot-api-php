# Примеры пользовательского запроса

Произвольные запросы выполняет `MaxHttpClient`, он доступен как `$apiClient->getMaxHttpClient()`.
Клиент сам подставляет базовый адрес, токен и заголовки, а ответ отдаёт разобранным массивом.

Методы: `get()`, `post()`, `patch()`, `put()`, `delete()` — путь, тело (где оно есть) и параметры строки запроса.
Отдельно есть `getUpdates()` — он принимает таймаут запроса в миллисекундах и выполняется клиентом для длинного опроса
событий. Метод `setVersion()` задаёт для последующих запросов версию API в параметре `v`.

## Примеры кода

### Получение информации о боте

```php
$response = $apiClient->getMaxHttpClient()->get('/me');
```

### Обновление списка команд бота

> **Примечание:** для обновления команд есть метод `MaxApiClient::editMyCommands()` — смотрите раздел
> [API-клиент](../ApiClient.md). Пример ниже показывает, как то же самое сделать произвольным запросом.

```php
$body = [
    'commands' => [
        ['name' => 'start', 'description' => 'Начать работу с ботом'],
        ['name' => 'help', 'description' => 'Получить справку'],
    ],
];

$response = $apiClient->getMaxHttpClient()->patch('/me/commands', (object)$body);
```

### Подписка на обновления

```php
$body = [
    'url' => 'https://your-domain.com/webhook',
    'update_types' => ['message_created', 'bot_started'],
    'secret' => 'your_secret',
];

$response = $apiClient->getMaxHttpClient()->post('/subscriptions', (object)$body);
```
