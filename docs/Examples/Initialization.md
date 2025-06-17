# Инициализация

## Рекомендации

- Для автоматической загрузки классов лучше всего использовать `composer`.
- Используйте [рекомендации официального API](https://dev.max.ru/docs-api#Рекомендации%20по%20работе%20с%20API).

## Примеры кода

### Создание API-клиента

#### Простая инициализация с токеном

```php
use MaxMessenger\Bot\MaxApiClient;

$apiClient = new MaxApiClient('your-access-token');
```

#### Инициализация с конфигурацией

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;
use Mj4444\SimpleHttpClient\CurlHttpClient;

$apiConfig = new MaxApiConfig(
    accessToken: 'your-access-token',
    httpClient: new CurlHttpClient(),
    baseUrl: 'https://platform-api.max.ru'
);
$apiClient = new MaxApiClient($apiConfig);
```

### Создание бота

#### Простая инициализация с токеном и секретом

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token', 'your-secret');
```

#### Инициализация с конфигурацией

```php
use MaxMessenger\Bot\MaxApiConfig;use MaxMessenger\Bot\MaxBot;use Mj4444\SimpleHttpClient\CurlHttpClient;

$apiConfig = new MaxApiConfig(
    accessToken: 'your-access-token',
    httpClient: new CurlHttpClient(),
    baseUrl: 'https://platform-api.max.ru'
);
$bot = new MaxBot($apiConfig, 'your-secret');
```

#### Инициализация с API-клиентом

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot($apiClient, 'your-secret');
```
