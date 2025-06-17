# Пользовательский запрос

## Примеры кода

### Получение информации о боте

```php
$response = $apiClient->getHttpClient()->get('/me');
```

### Обновление списка команд бота

```php
$body = [
    'commands' => [
        ['name' => 'start', 'description' => 'Начать работу с ботом'],
        ['name' => 'help', 'description' => 'Получить справку'],
    ],
];

$response = $apiClient->getHttpClient()->patch('/me', (object)$body);
```

### Подписка на обновления

```php
$body = [
    'url' => 'https://your-domain.com/webhook',
    'update_types': ['message_created', 'bot_started'],
    'secret': 'your_secret',
];

$response = $apiClient->getHttpClient()->post('/subscriptions', (object)$body);
```
