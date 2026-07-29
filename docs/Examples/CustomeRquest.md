# Примеры пользовательского запроса

## Примеры кода

### Получение информации о боте

```php
$response = $apiClient->getHttpClient()->get('/me');
```

### Обновление списка команд бота

> **Примечание:** для обновления команд есть штатные методы `MaxApiClient::editMyCommands()`
> и `MaxApiClient::editMyInfo()` — смотрите раздел [API-клиент](../ApiClient.md).
> Пример ниже показывает, как то же самое сделать произвольным запросом.

```php
$body = [
    'commands' => [
        ['name' => 'start', 'description' => 'Начать работу с ботом'],
        ['name' => 'help', 'description' => 'Получить справку'],
    ],
];

$response = $apiClient->getHttpClient()->patch('/me/commands', (object)$body);
```

### Подписка на обновления

```php
$body = [
    'url' => 'https://your-domain.com/webhook',
    'update_types' => ['message_created', 'bot_started'],
    'secret' => 'your_secret',
];

$response = $apiClient->getHttpClient()->post('/subscriptions', (object)$body);
```
