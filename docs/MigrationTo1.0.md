# Миграция с 0.x на 1.0

Руководство сравнивает версию `0.3.10` с `1.0`. Если вы переходите с более ранней версии `0.x`,
проверьте также изменения между вашей версией и `0.3.10`.

## Обновление зависимости

Обновите ограничение версии в проекте:

```bash
composer require max-messenger-bot/max-bot-api-php:^1.0
```

Минимальная версия PHP осталась `8.2`, расширение `ext-mbstring` обязательно.
Для встроенного HTTP-клиента нужен `ext-curl`; вместо него можно настроить клиент PSR-18.

Пакет `mj4444/simple-http-client` больше не является зависимостью SDK. Если ваше приложение использует его напрямую,
объявите эту зависимость в своём `composer.json`.

Простой код с `new MaxApiClient('токен')`, отправкой сообщений и `new MaxBot('токен', 'секрет')` менять не требуется.
Далее изменения сгруппированы по частям SDK: конфигурация и HTTP, ошибки, обработчики, модели и утилиты.

## HTTP-клиент и конфигурация

### Конструктор и выбор транспорта

В `MaxApiConfig` больше нет аргумента конструктора `httpClient`. Передавайте свой транспорт через `setHttpClient()`;
он должен реализовывать `MaxMessenger\Bot\Contract\Http\HttpClientInterface`:

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;

$config = new MaxApiConfig('your-access-token', useRussianTrustedCaCertificates: true);
$config->setHttpClient(
    $config->makeCurlHttpClient()->setProxy('127.0.0.1:1080', socks5: true),
);
$apiClient = new MaxApiClient($config);
```

Вторым аргументом конструктора `MaxApiConfig` вместо `httpClient` стал `bool $useRussianTrustedCaCertificates`,
`baseUrl` остался третьим. Вызов `new MaxApiConfig($token, null, $url)` или `new MaxApiConfig($token, $httpClient)`
теперь выбросит `TypeError`. URL передавайте именованным аргументом `baseUrl:`, а транспорт — через `setHttpClient()`.

### Сертификаты

CA-бандл Минцифры по-прежнему не подключается по умолчанию. Вместо вызова `useRussianTrustedCaCertificates()`
его можно включить параметром конструктора `useRussianTrustedCaCertificates: true` — у `MaxApiConfig`,
а также у `MaxApiClient` и `MaxBot`, когда они создаются из токена.
Заданный через `setCaCertificatePath()` CA-бандл по-прежнему заменяет бандл пакета.

```php
use MaxMessenger\Bot\MaxApiClient;

$apiClient = new MaxApiClient('your-access-token', useRussianTrustedCaCertificates: true);
```

### Транспорты для опроса и загрузки

Загрузка медиафайлов больше не использует основной клиент автоматически. Если клиент для загрузки не задан через
`setHttpClientForUploader()`, `MaxApiConfig::getHttpClientForUploader()` создаёт отдельный `CurlHttpClient`, который
проверяет серверы загрузки по системным сертификатам. Если основной клиент — PSR-18 или curl-клиент с прокси
и загрузка должна идти через него, передайте его также в `setHttpClientForUploader()`.

Для длинного опроса можно задать отдельный транспорт через `setHttpClientForUpdates()`.

`makeCurlHttpClient()` создаёт клиент с таймаутами и параметрами TLS из конфигурации.
Если используете PSR-18, см. [выбор HTTP-клиента](ApiClient.md#выбор-http-клиента).
Настройки транспорта, включая сертификаты, для своего PSR-18 клиента задавайте в нём самом.

### Доступ к HTTP-клиенту и собственные реализации

Для доступа к клиенту API метод `MaxApiClient::getHttpClient()` переименован в `getMaxHttpClient()`.
Сам транспорт доступен через `$apiClient->getMaxHttpClient()->getHttpClient()`.

Если вы реализовали `MaxApiConfigInterface` или `MaxHttpClientInterface` самостоятельно, обновите реализации по новым
сигнатурам: интерфейсы используют собственные HTTP-контракты SDK. В частности, таймаут удалён из
`MaxHttpClientInterface::get()` и передаётся через новый `getUpdates()` для длинного опроса;
`withVersion()` заменён на `setVersion()`. В отличие от `withVersion()`, он не создаёт новый клиент, а меняет версию
у текущего — она применится ко всем последующим запросам через этот клиент.

В `MaxApiConfigInterface` добавлены методы `getAttachmentRetryAttempts()`, `getHttpClientForUpdates()`,
`getHttpClientForUploader()` и `getPostRetryAttempts()`.
Метод `getHttpClientForUploader()` всегда возвращает клиент.

Метод `MaxApiConfigInterface::getMaxHttpClient()` удалён, и `MaxApiClient` его больше не вызывает. Если ваша
конфигурация возвращала из него свой `MaxHttpClientInterface`, передайте этот клиент в конструктор:
`new MaxApiClient($config, httpClient: $maxHttpClient)`.

Подробнее — в [документации API-клиента](ApiClient.md).

### Низкоуровневые HTTP-модели

Если приложение напрямую использовало низкоуровневые классы `JsonRequest`, `JsonResponse` или `JsonBody`,
обновите этот код для новых `HttpRequest`, `HttpResponse` и `StringBody` либо используйте готовый `CurlHttpClient`
или `PsrHttpClient`. Эти классы не являются заменами с прежними сигнатурами конструктора.

## Повторы запросов и обработка ошибок

### Расписания повторов

Свойство `MaxApiClient::$retryAttempts` удалено. Теперь все расписания задаются в `MaxApiConfig`:

```php
$config = new MaxApiConfig('your-access-token', useRussianTrustedCaCertificates: true);
$config->setRetryAttempts([1000, 2000, 4000]);     // временные ошибки транспорта и API
$config->setPostRetryAttempts([1000, 2000]);       // отдельное расписание для POST
$config->setAttachmentRetryAttempts([1000, 2000]); // attachment.not.ready
$apiClient = new MaxApiClient($config);
```

Значения указаны в миллисекундах. Для двух дополнительных расписаний `null` означает использование общего расписания,
а `[]` отключает соответствующие повторы. Подробнее — в [описании ошибок и повторов](ApiClient.md#ошибки).

Набор повторяемых ошибок расширен: кроме сетевых ошибок и ответов `429`, `500` и `503`
теперь повторяются также ответы `502` и `504`.

### Логгер повторов

Логгер повторных попыток больше не передаётся вторым аргументом конструктора `MaxApiClient`.
Настройте его после создания клиента через `setExceptionLogger()`; тип исключения в обработчике также изменился:

```php
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;

$apiClient = (new MaxApiClient($config))->setExceptionLogger(
    static function (string $method, TemporaryExceptionInterface|BadRequestException $exception): void {
        error_log($method . ': ' . $exception->getMessage());
    },
);
```

### Исключения HTTP-слоя

Классы исключений кодирования запроса и разбора ответа перенесены из `MaxMessenger\Bot\Exception\HttpClient\...`
в `MaxMessenger\Bot\HttpClient\Exception\...`. Базовый класс HTTP-ошибок `MaxHttpException` заменён на `HttpException`;
сетевые ошибки представлены `TemporaryNetworkException`, а таймауты — наследующим его `TimeoutException`.
Проверьте свои `use` и `catch`.

Исключения пакета `mj4444/simple-http-client` больше не выбрасываются, поэтому `catch` по его
`HttpClientException` перестанет срабатывать. Все ошибки HTTP-слоя теперь наследуют `MaxApiException` и делятся
на три ветви: `HttpRequestException` (запрос не удалось подготовить), `HttpClientException` (ответ не получен)
и `HttpResponseException` (получен ответ с ошибкой или его не удалось разобрать). Новый `HttpClientException`
не покрывает ошибки ответа: чтобы перехватить все ошибки HTTP, используйте `MaxApiException`.
Исключение — клиент PSR-18: в `TemporaryNetworkException` и `TimeoutException` заворачиваются только его сетевые ошибки
(`NetworkExceptionInterface`), таймауты и ошибки с кодом временной ошибки curl в тексте, прочие исключения клиента
PSR-18 пробрасываются как есть.

`getError()` у всех HTTP-исключений (`HttpException` и его наследников, включая `BadRequestException`) теперь может
вернуть `null`. Если вы проверяли `attachment.not.ready` через `$exception->isAttachmentNotReady()`,
используйте `$exception->getError()?->isAttachmentNotReady() === true`.

Без разобранного `Error` сообщение HTTP-исключения имеет вид `HTTP error 404`, а не содержит тело ответа.
Полное тело доступно через `$exception->getResponse()->getBody()`.

### Ошибки выполнения операций

Если вы определяли ошибку `SimpleQueryError` по тексту `getMessage()`, перейдите на проверку `getErrorCode()`.
При самостоятельном создании `SimpleQueryError` передавайте в конструктор объект `SimpleQueryResult`
вместо сообщения и числового кода исключения.

`MaxApiClient::addMembers()` теперь возвращает `void` вместо `ModifyMembersResult`.
Замените проверку `isSuccess()` у возвращённого результата обработкой `ModifyMembersError`
из пространства имён `MaxMessenger\Bot\Exception`. Исключение наследует `SimpleQueryError`.
Вызовы `getFailedUserIds()` и `getFailedUserDetails()` перенесите с результата на исключение.

## Обработчики событий

### Статус обработки

Обработчик конкретного события, типа обновления или `onFallback()`, который завершился без `return`,
теперь по умолчанию отмечает событие обработанным. Это также относится к обработчикам команд и callback.
Если должны выполняться следующие обработчики, явно верните `false` или вызовите `$event->markAsUnhandled()`:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCreatedEvent;

$bot->onMessageCreated(static function (MessageCreatedEvent $event): bool {
    // Обработка сообщения без остановки следующих обработчиков.
    return false;
});
```

Для `onPrepare()` и `onFinal()` завершение без `return` по-прежнему само по себе не меняет статус события.
Это относится и к обработчикам исключений, зарегистрированным через `onException()`.
Подробнее — в [обработке событий](ProcessingEvents.md) и [команд](ProcessingCommands.md).

### Фильтрация обновлений вместо `hasMessage()`

Метод `hasMessage()` удалён из событий сообщений и callback, использующих `MessageEventTrait`.
Вместо него используется механизм фильтрации всех обновлений через калбэк `MaxBot::setUpdateFilter()`.

По умолчанию фильтр вызывает `$update->isValid()` до создания события. Обновления сообщений и callback без поля
`message` не доходят до обработчиков. Удалите прежние проверки наличия сообщения из обработчиков:
`getMessage()` теперь возвращает `Message` и не прерывает обработку через `continue()` при отсутствии сообщения.

Собственные условия можно объединить с проверкой обязательных полей. Например, чтобы также отсеивать
обновления с неизвестным SDK типом:

```php
use MaxMessenger\Bot\Model\Response\Update;

$bot->setUpdateFilter(static fn(Update $update): bool => $update->isValid() && $update->getUpdateType() !== null);
```

Текущий калбэк доступен через `getUpdateFilter()`, а `setUpdateFilter(null)` отключает фильтрацию.
Если вы вызываете `makeEvent()` или `handleEvent()` напрямую, фильтр автоматически не выполняется — вызовите его перед
созданием события. Подробнее и примеры — в разделе [Фильтрация обновлений](ProcessingUpdates.md#фильтрация-обновлений).

### Секрет Webhook

`MaxBot::setSecret()` и аргумент `secret` конструктора `MaxBot` теперь проверяют длину секрета: от 5 до 256 символов.
Более короткий секрет приводит к `MinLengthException`, более длинный — к `MaxLengthException`.
Раньше пустая строка молча отключала проверку заголовка `X-Max-Bot-Api-Secret`, теперь она тоже приводит
к `MinLengthException`. Чтобы отключить проверку, передавайте `null`, например
`new MaxBot($token, getenv('MAX_SECRET') ?: null)`.

## Команды

### Регистрация и настройка

Регистрировать команды теперь можно непосредственно через `MaxBot::onCommand()` и `MaxBot::onCommands()`.
Способ через `getCommandHandler()` остаётся доступен, но этот метод больше не принимает параметры.
Разделитель основного обработчика задавайте через `setCommandSeparator()`:

```php
// Было:
$commandHandler = $bot->getCommandHandler(' ', 64);

// Стало:
$commandHandler = $bot->getCommandHandler()->setCommandSeparator(' ');
```

Раньше обработчик принимал команды только из личных диалогов с ботом. Теперь он также принимает команды
из групповых чатов: проверьте, что ваши обработчики рассчитаны на вызов в группе.
Для обработки команд из каналов добавлен параметр `allowChannels` (по умолчанию `false`).
Передайте `allowChannels: true` в конструктор или включите настройку основного обработчика:

```php
$bot->getCommandHandler()->setAllowChannels(true);
```

### Собственный `CommandHandler`

Изменён порядок параметров конструктора: `commandSeparator`, `allowChannels`, `commandHandlers`, `commandsHandlers`.
Перенесите массивы обработчиков в конец позиционного вызова:

```php
use MaxMessenger\Bot\MaxBot\CommandHandler;

// Было:
$commandHandler = new CommandHandler($commandHandlers, $commandsHandlers, ' ', 64);

// Стало:
$commandHandler = new CommandHandler(' ', false, $commandHandlers, $commandsHandlers);
```

Параметр и свойство `commandMaxLength`, а также методы `getCommandMaxLength()` и `setCommandMaxLength()` удалены.
Уберите их из позиционных и именованных вызовов. Обработчик больше не ограничивает длину имени команды.

Отдельный экземпляр можно зарегистрировать через `onMessageCreated()` с новым параметром `prepend: true`,
чтобы он выполнялся перед остальными обработчиками сообщений:

```php
$bot->onMessageCreated($commandHandler->handle(...), prepend: true);
```

## Обработчики callback

### Регистрация и настройка

Методы `MaxBot::addCallbackHandler()` и `MaxBot::addCallbackJsonHandler()` удалены.
Для регистрации действий используйте новые методы `MaxBot::onCallback()` и `MaxBot::onCallbackJson()`:

```php
// Было:
$callbackHandler = $bot->addCallbackHandler();
$callbackHandler->onAction('help', $helpHandler);
$callbackJsonHandler = $bot->addCallbackJsonHandler('action');
$callbackJsonHandler->onAction('buy', $buyHandler);

// Стало:
$bot->onCallback('help', $helpHandler);
$bot->onCallbackJson('buy', $buyHandler);
```

Для настройки основного обработчика используйте `getCallbackHandler()` или `getCallbackJsonHandler()`.
Например, разделитель текстового payload задаётся до регистрации действий:

```php
$bot->getCallbackHandler()->setActionSeparator(':');
$bot->onCallback('buy', $buyHandler);
```

Ключ JSON-поля с именем действия по умолчанию — `action`. Для другого ключа настройте основной обработчик:

```php
$bot->getCallbackJsonHandler()->setActionKey('cmd');
$bot->onCallbackJson('buy', $buyHandler);
```

У `CallbackJsonHandler` параметр конструктора `actionKey` теперь необязателен и по умолчанию равен `action`.
Свойство `actionKey` можно изменять, в том числе через новый метод `setActionKey()`.

### Собственные обработчики

В `CallbackHandler` изменён порядок параметров конструктора: `actionSeparator`, `actionHandlers`.
Параметр `actionMaxLength` удалён:

```php
use MaxMessenger\Bot\MaxBot\CallbackHandler;

// Было:
$callbackHandler = new CallbackHandler($actionHandlers, ':', 64);

// Стало:
$callbackHandler = new CallbackHandler(':', $actionHandlers);
```

В `CallbackHandler` и `CallbackJsonHandler` удалены свойство `actionMaxLength` и методы `getActionMaxLength()` и
`setActionMaxLength()`. Удалите обращения к ним. Обработчики больше не ограничивают длину имени действия.

Если нужны отдельные экземпляры обработчиков, создайте их самостоятельно
и зарегистрируйте через `onMessageCallback()`:

```php
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;

$callbackHandler = new CallbackHandler(':');
$bot->onMessageCallback($callbackHandler->handle(...));

$callbackJsonHandler = new CallbackJsonHandler('cmd');
$bot->onMessageCallback($callbackJsonHandler->handle(...));
```

### Ответ на нажатие кнопки

У `MessageCallbackEvent::answer()` удалён параметр `notification`: второй аргумент теперь `disableLinkPreview`.
Проверьте вызовы с позиционными аргументами: прежний третий аргумент `disableLinkPreview` теперь второй.
Метод `answerNotification()` удалён из SDK.

```php
// Было:
$event->answer($message, null, true);

// Стало:
$event->answer($message, disableLinkPreview: true);
```

## Кнопки

### Удалённые параметры и типы

У callback-кнопок удалён параметр `intent`. Передавайте в `addCallbackButton()` текст и payload без третьего аргумента.
Перечисление `Intent` удалено, а у `CallbackButton` в ответах API удалены методы `getIntent()` и `getIntentRaw()`.
Класс `ChatButton` и метод `addChatButton()` удалены вместе с соответствующей возможностью API.

### Открытие мини-приложения

В `InlineKeyboardAttachmentRequestPayload::addOpenAppButton()` и `OpenAppButton::make()` аргумент `webApp`
теперь обязателен. Если раньше вы передавали только `contactId`, добавьте публичное имя бота, чьё мини-приложение
нужно открыть. Метод `unsetWebApp()` удалён.

## Получение сообщений и обновлений

В `MaxApiClient::getMessages()` и `getMessagesFromChat()` аргументы `from` и `to` переименованы в `before` и `after`.
Обновите именованные вызовы: `from:` замените на `before:`, а `to:` — на `after:`. Порядок позиционных аргументов
сохранился. Значения по-прежнему задаются в Unix-времени в миллисекундах.

Таймаут длинного опроса в `MaxApiClient::getUpdates()` по умолчанию уменьшен с 60 до 30 секунд.
Чтобы сохранить прежнее время ожидания обновлений, передайте `timeout: 60` явно.

## Модели вложений и права администратора

### Типы вложений

Перечисление `AttachmentRequestType` удалено. Используйте `AttachmentType` при создании собственных наследников
`AttachmentRequest` и при проверке результата `AttachmentRequest::getType()`.

Кейс `AttachmentType::Data` удалён: вложений типа `data` в API нет.

### Превью ссылки

Из `NewMessageBody::addShareAttachment()` удалён аргумент `token`. Оставьте только URL.
Старый второй позиционный аргумент будет проигнорирован, а именованный `token:` вызовет ошибку.
Если токен нужен, создайте `ShareAttachmentRequest` с `ShareAttachmentPayload($url, $token)` и передайте вложение
в `NewMessageBody::addAttachment()`.

### Права администратора

Из `ChatAdminPermission` удалены кейсы `DeleteMessage`, `EditMessage` и `PostEditDeleteMessage`.
Обновите обращения к ним и сохранённые строковые значения `delete_message`, `edit_message`, `post_edit_delete_message`.
Выбирайте актуальные права по назначению: `Write` — для удаления чужих сообщений в групповом чате и публикации
в канале, `Edit` и `Delete` — для редактирования и удаления постов и комментариев в канале.
Описание остальных прав см. в `ChatAdminPermission`.

## Остальные удалённые и переименованные методы

| В `0.3.10`                                              | В `1.0`                                                                        |
|---------------------------------------------------------|--------------------------------------------------------------------------------|
| `MaxApiClient::getChatByLink()`                         | Метод удалён: соответствующий запрос API больше не работает.                   |
| `MaxApiClient::getChats()`                              | Метод удалён: соответствующий запрос API больше не работает.                   |
| `MaxApiClient::editMyInfo()`                            | Используйте `editMyCommands()`.                                                |
| `$event->sendMessage()`, `sendToChat()`, `sendToUser()` | Используйте `sendMessageToUser()` или `sendMessageToChat()`.                   |
| `$event->sendAction()`                                  | Используйте `sendActionToChat()`. Метод теперь возвращает `void`, а не `bool`. |
| `CommentMessage::getUrl()`                              | Метод удалён: модель комментария больше не содержит публичную ссылку.          |
| `AudioAttachment::getTranscription()`                   | Удалите вызовы: API никогда не возвращал поле `transcription`.                 |

## Консольные утилиты

Переименуйте файлы конфигурации в корне проекта или в каталоге `dev/`:

- `.tokens` → `.max-tokens`;
- `.polling-to-webhook.conf` → `.max-polling-to-webhook.conf`.

Старые имена больше не используются. Примеры новых файлов находятся в каталоге `dev/` пакета.

Утилиты `max-chats` и `max-delete-chats` удалены вместе с возможностью получения списка чатов через API.

Если вы загружаете медиафайлы, см. [Загрузка медиафайлов](UploadingFiles.md). Изменения загрузчика описаны в
[руководстве по миграции с 0.2.1 на 1.0](https://github.com/max-messenger-bot/max-bot-uploader-php/blob/main/docs/MigrationTo1.0.md).
