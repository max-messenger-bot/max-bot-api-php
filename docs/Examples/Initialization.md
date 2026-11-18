# Примеры инициализации API-клиента и бота

## Рекомендации

- Для автоматической загрузки классов лучше всего использовать `composer`.
- Используйте [рекомендации официального API](https://dev.max.ru/docs-api#Рекомендации%20по%20работе%20с%20API).

## Примеры кода

### Создание API-клиента

#### Простая инициализация с токеном

```php
use MaxMessenger\Bot\MaxApiClient;

$apiClient = new MaxApiClient('your-access-token', useRussianTrustedCaCertificates: true);
```

#### Инициализация с конфигурацией

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;

$apiConfig = new MaxApiConfig(
    accessToken: 'your-access-token',
    useRussianTrustedCaCertificates: true,
    baseUrl: 'https://platform-api2.max.ru'
);
$apiConfig->setConnectTimeout(3000);
$apiConfig->setTimeout(30000);
$apiClient = new MaxApiClient($apiConfig);
```

Запросы по умолчанию выполняет встроенный клиент на curl: `MaxApiConfig` создаёт его сам и применяет
заданные таймауты и параметры TLS.

#### Свой HTTP-клиент

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;

$apiConfig = new MaxApiConfig('your-access-token', useRussianTrustedCaCertificates: true);
$apiConfig->setHttpClient(
    $apiConfig->makeCurlHttpClient()->setProxy('127.0.0.1:1080', socks5: true)
);
$apiClient = new MaxApiClient($apiConfig);
```

> Клиент, созданный как `new CurlHttpClient()`, таймауты и параметры TLS из конфигурации не получает —
> берите преднастроенный у `makeCurlHttpClient()`. Как подключить клиент PSR-18 (Guzzle, Symfony HttpClient) и как
> выделить отдельный клиент под опрос событий, описано в разделе [API-клиент](../ApiClient.md#выбор-http-клиента).

#### Настройка TLS: доверенный CA или отключение проверки

Сертификат API Max выпущен удостоверяющим центром Минцифры, которого нет в системном хранилище
доверенных корней на большинстве систем. По умолчанию используются системные сертификаты. Поставляемый
с пакетом CA-бандл Минцифры подключается параметром конструктора `MaxApiClient`, `MaxApiConfig` или `MaxBot`:

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\MaxBot;

$apiClient = new MaxApiClient('your-access-token', useRussianTrustedCaCertificates: true);
// либо
$apiConfig = new MaxApiConfig('your-access-token', useRussianTrustedCaCertificates: true);
// либо
$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);
```

Бандл применяется только к запросам к API: серверы загрузки медиафайлов проверяются по системным сертификатам.

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

> Настройки TLS применяются только к встроенному клиенту на curl, который `MaxApiConfig` создаёт сам или отдаёт методом
> `makeCurlHttpClient()`. Если вы задаёте свой клиент через `setHttpClient()`, настраивайте TLS непосредственно в нём.

#### Добавление логгера исключений

```php
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\MaxApiClient;

$exceptionLogger = static function (
    string $method,
    TemporaryExceptionInterface|BadRequestException $exception,
): void {
    echo sprintf("%s [%s] %s\n", date('H:i:s'), $method, $exception->getMessage());
};
$apiClient = (new MaxApiClient($accessTokenOrConfig))
    ->setExceptionLogger($exceptionLogger);
```

### Создание бота

#### Простая инициализация с токеном и секретом

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);
```

#### Инициализация с конфигурацией

```php
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\MaxBot;

$apiConfig = new MaxApiConfig(
    accessToken: 'your-access-token',
    useRussianTrustedCaCertificates: true,
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
