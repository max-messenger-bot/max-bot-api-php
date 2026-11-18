# Загрузка медиафайлов на серверы МАКС

Для загрузки медиафайлов установите
[max-messenger-bot/max-bot-uploader-php](https://github.com/max-messenger-bot/max-bot-uploader-php) — пакет того же
разработчика, что и этот SDK:

```bash
composer require max-messenger-bot/max-bot-uploader-php
```

`MaxUploader` загружает медиафайл с поддержкой возобновления и возвращает токен вложения.
Передайте этот токен в сообщение через `NewMessageBody`:

```php
require __DIR__ . '/vendor/autoload.php';

use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\Model\Request\NewMessageBody;
use MaxMessenger\Uploader\MaxUploader;

$apiClient = new MaxApiClient('your-access-token', useRussianTrustedCaCertificates: true);
$uploader = new MaxUploader($apiClient);

$fileToken = $uploader->uploadFile(__DIR__ . '/document.pdf');
$message = NewMessageBody::new()->addFileAttachment($fileToken);

$apiClient->sendMessageToUser(12345678, $message);
```

Для изображений, видео и аудио используйте соответствующие методы `addImageAttachment()`,
`addVideoAttachment()` и `addAudioAttachment()` вместо `addFileAttachment()`.
Подробнее об отправке вложений — в разделе [Отправка сообщений](SendingMessages.md#вложения).

Для других способов загрузки и настройки возобновления смотрите
[документацию пакета загрузки](https://github.com/max-messenger-bot/max-bot-uploader-php#документация).
