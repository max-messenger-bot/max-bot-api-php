# Российские доверенные сертификаты (Минцифры)

`russian_trusted_ca_bundle.pem` — CA-бандл национального удостоверяющего центра Минцифры России
для проверки TLS-сертификата API Max (`platform-api2.max.ru`), цепочка которого выпущена этим
центром и отсутствует в системном хранилище доверенных корней на большинстве систем.

## Использование

```php
use MaxMessenger\Bot\MaxApiConfig;

$apiConfig = new MaxApiConfig('your-access-token');
$apiConfig->useRussianTrustedCaCertificates();
```

Метод указывает `caCertificatePath` на этот бандл. При необходимости можно задать свой путь через
`setCaCertificatePath()` / `setCaCertificateDir()`.

Источник: [Госуслуги — Российский TLS-сертификат](https://www.gosuslugi.ru/tls).
