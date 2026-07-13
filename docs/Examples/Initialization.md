# Примеры инициализации API-клиента и бота

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
    baseUrl: 'https://platform-api2.max.ru'
);
$apiClient = new MaxApiClient($apiConfig);
```

#### Настройка TLS: доверенный CA или отключение проверки

Сертификат API Max выпущен удостоверяющим центром Минцифры, которого нет в системном хранилище
доверенных корней на большинстве систем. Чтобы проверка проходила, включите поставляемый с пакетом
CA-бандл Минцифры:

```php
use MaxMessenger\Bot\MaxApiConfig;

$apiConfig = new MaxApiConfig('your-access-token');
$apiConfig->useRussianTrustedCaCertificates();
```

Либо укажите свой CA-бандл или каталог с сертификатами:

```php
use MaxMessenger\Bot\MaxApiConfig;

$apiConfig = new MaxApiConfig('your-access-token');
$apiConfig->setCaCertificatePath('/path/to/ca-bundle.pem');
// либо каталог с сертификатами:
// $apiConfig->setCaCertificateDir('/path/to/certs');
```

Проверку сертификата можно отключить. Это небезопасно и предназначено только для локальной отладки:

```php
use MaxMessenger\Bot\MaxApiConfig;

$apiConfig = new MaxApiConfig('your-access-token');
$apiConfig->setVerifySslCertificate(false);
```

> Настройки TLS применяются только к встроенному HTTP-клиенту. Если вы передаёте собственный
> `httpClient`, настраивайте TLS непосредственно в нём.

#### Добавление логгера исключений

```php
use MaxMessenger\Bot\MaxApiClient;
use Mj4444\SimpleHttpClient\Exceptions\HttpClientException;

$exceptionLogger = static function (string $method, HttpClientException $exception): void {
    echo sprintf("%s [%s] %s\n", date('H:i:s'), $method, $exception->getMessage());
};
$apiClient = new MaxApiClient($accessTokenOrConfig, $exceptionLogger);
```

### Создание бота

#### Простая инициализация с токеном и секретом

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token', 'your-secret');
```

#### Инициализация с конфигурацией

```php
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\MaxBot;
use Mj4444\SimpleHttpClient\CurlHttpClient;

$apiConfig = new MaxApiConfig(
    accessToken: 'your-access-token',
    httpClient: new CurlHttpClient(),
    baseUrl: 'https://platform-api2.max.ru'
);
$apiConfig->setRetryAttempts([1000, 2000, 4000, 8000, 15000]);
$bot = new MaxBot($apiConfig, 'your-secret');
```

#### Инициализация с API-клиентом

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot($apiClient, 'your-secret');
```
