# Пользовательский запрос

## Примеры кода

### Получение информации о боте

```php
$response = $apiClient->getHttpClient()->get('/me');
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
