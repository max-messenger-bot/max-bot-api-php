# API-клиент

## Введение

`MaxApiClient` — основной класс для прямого взаимодействия с API Max Messenger Platform. Он предоставляет
типизированные методы для всех поддерживаемых операций API, включая управление чатами, отправку сообщений,
работу с участниками и получение событий.

### Ключевые возможности

- **Управление чатами** — создание, редактирование, удаление групповых чатов, управление закреплёнными сообщениями
- **Управление участниками** — добавление и удаление участников, назначение администраторов, управление правами
- **Работа с сообщениями** — отправка, редактирование, удаление сообщений, получение истории сообщений
- **Комментарии в каналах** — отправка, получение, редактирование и удаление комментариев к постам
- **Callback-ответы** — отправка ответов на интерактивные кнопки
- **События** — получение событий через Long Polling, управление WebHook-подписками
- **Информация о боте** — получение и редактирование информации о боте, управление списком команд бота
- **Действия бота** — отправка индикаторов действий (например, "печатает")
- **Загрузка медиафайлов** — получение URL для загрузки медиафайлов, информация о видео

### Инициализация

Простая инициализация с токеном:

```php
use MaxMessenger\Bot\MaxApiClient;

$client = new MaxApiClient('your-access-token', useRussianTrustedCaCertificates: true);
```

Параметр `useRussianTrustedCaCertificates` подключает поставляемый с пакетом CA-бандл Минцифры,
которым подписан сертификат API Max. По умолчанию используются системные сертификаты.

Инициализация с конфигурацией и логгером исключений (в него попадают ошибки, после которых запрос повторяется):

```php
use MaxMessenger\Bot\HttpClient\Exception\HttpResponse\Http\BadRequestException;
use MaxMessenger\Bot\HttpClient\Exception\TemporaryExceptionInterface;
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;

$config = new MaxApiConfig(
    accessToken: 'your-access-token',
    useRussianTrustedCaCertificates: true,
    baseUrl: 'https://platform-api2.max.ru'
);
$client = new MaxApiClient($config);

$exceptionLogger = static function (
    string $method,
    TemporaryExceptionInterface|BadRequestException $exception,
): void {
    echo sprintf("%s [%s] %s\n", date('H:i:s'), $method, $exception->getMessage());
};
$client->setExceptionLogger($exceptionLogger);
```

Параметр конструктора `httpClient` принимает готовый `MaxHttpClientInterface`:
`new MaxApiClient($config, httpClient: $httpClient)`. Если клиент не передан, создаётся стандартный `MaxHttpClient`.
Логгер исключений задаётся через `setExceptionLogger()` и передаётся используемому HTTP-клиенту.

### Особенности использования

- Все методы API имеют строгую типизацию и встроенную валидацию параметров
- Для работы с моделями запросов используются классы из `MaxMessenger\Bot\Model\Request\`
- Модели ответов возвращаются из `MaxMessenger\Bot\Model\Response\`
- HTTP-клиент доступен через `$client->getMaxHttpClient()` для выполнения произвольных запросов
- Метод `sendMessage()` автоматически обрабатывает ошибки готовности вложений с повторными попытками
- Все методы автоматически делают повторные попытки при сетевых ошибках

## Выбор HTTP-клиента

Запросы к API выполняет один из двух клиентов. Оба реализуют `MaxMessenger\Bot\Contract\Http\HttpClientInterface`,
принимают `HttpRequest` и возвращают `HttpResponse`, поэтому остальной код от выбора не зависит.

### CurlHttpClient (по умолчанию)

Встроенный клиент на curl. Отдельной настройки не требует: его создаёт `MaxApiConfig`,
применяя заданные таймауты и параметры TLS. Нужен модуль `ext-curl`.

Собственный экземпляр нужен, только если требуются особые настройки.
Создавайте его методом `MaxApiConfig::makeCurlHttpClient()`: он возвращает клиент с уже
применёнными таймаутами и параметрами TLS из конфигурации, остаётся задать только своё.

```php
use MaxMessenger\Bot\MaxApiClient;
use MaxMessenger\Bot\MaxApiConfig;

$config = new MaxApiConfig('your-access-token', useRussianTrustedCaCertificates: true);
$httpClient = $config->makeCurlHttpClient()
    ->setConnectTimeout(3000)
    ->setTimeout(30000)
    ->setUserAgent('my-bot/1.0')
    ->setProxy('127.0.0.1:1080', socks5: true);

$config->setHttpClient($httpClient);
$client = new MaxApiClient($config);
```

> **Внимание:** клиент, созданный как `new CurlHttpClient()`, о конфигурации ничего не знает — таймауты
> и параметры TLS из `MaxApiConfig`, в том числе CA-бандл Минцифры, к нему не применяются. Задавайте их сами
> либо берите преднастроенный клиент у `makeCurlHttpClient()`.

Настройки клиента: `setConnectTimeout()`, `setTimeout()`, `setUserAgent()`, `setProxy()`,
`setVerifySslCertificate()`, `setCaCertificatePath()`, `setCaCertificateDir()`, `setUseNativeCaCertificates()`
(системное хранилище сертификатов, если его поддерживает libcurl). Любой другой параметр curl
задаётся методом `setOption()`, а `resetOptions()` сбрасывает все заданные параметры.

Ошибки curl можно классифицировать своим callback: он получает `CurlException` и возвращает
`TransportErrorType::Timeout`, `TransportErrorType::TemporaryNetwork` или `TransportErrorType::Permanent`.
`null` в результате оставляет встроенную классификацию. Callback передают вторым аргументом конструктора
или задают через `setErrorClassifier()` у клиента из `makeCurlHttpClient()`.

Публичное свойство `$usePersistentCurlHandle` (по умолчанию `true`) сохраняет дескриптор curl между запросами.
Это позволяет curl повторно использовать соединение, когда оно доступно.
Публичное свойство `$postDataStringThreshold` (по умолчанию `256 * 1024` байт) задаёт максимальный размер
потокового тела, которое передаётся строкой через `CURLOPT_POSTFIELDS`. Более крупное тело отправляется потоком.

### PsrHttpClient (любой клиент PSR-18)

Подходит, когда в проекте уже есть настроенный HTTP-клиент — Guzzle, Symfony HttpClient и другие.
Требует пакеты `psr/http-client`, `psr/http-factory` и `psr/http-message`, а также саму реализацию.

**Guzzle** — сам клиент не является фабрикой PSR-17, поэтому фабрики `HttpFactory` передаются явно:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;

$factory = new HttpFactory();
$httpClient = new PsrHttpClient(new Client(['timeout' => 30]), $factory, $factory);

$config = (new MaxApiConfig('your-access-token'))->setHttpClient($httpClient);
```

**Symfony HttpClient** — через адаптер `Psr18Client`, который сам реализует обе фабрики:

```php
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;
use Symfony\Component\HttpClient\Psr18Client;

$config = (new MaxApiConfig('your-access-token'))->setHttpClient(new PsrHttpClient(new Psr18Client()));
```

> **Внимание:** `PsrHttpClient` игнорирует таймаут отдельного запроса и ограничения скорости передачи.
> Задавайте их при настройке используемого клиента PSR-18. Учитывайте long polling: `getUpdates()` по умолчанию
> ждёт ответа 30 секунд, а максимум — 90, и прерывает запрос на 10 секунд позже, поэтому таймаут клиента должен
> быть больше. Под опрос событий удобнее выделить отдельный клиент — смотрите следующий раздел.

Четвёртый аргумент `PsrHttpClient` принимает callback для классификации ошибок PSR-18. Он получает
`ClientExceptionInterface` и возвращает `TransportErrorType::Timeout`, `TransportErrorType::TemporaryNetwork` или
`TransportErrorType::Permanent`. Последний вариант оставляет исходное исключение без повтора.
Если callback вернёт `null` или не задан, применяется встроенная классификация. Например:

```php
use MaxMessenger\Bot\HttpClient\TransportErrorType;

$httpClient = new PsrHttpClient(
    $psrClient,
    $requestFactory,
    $streamFactory,
    static fn($error): ?TransportErrorType => $error instanceof MyTimeoutException
        ? TransportErrorType::Timeout
        : null,
);
```

### Отдельные клиенты для опроса событий и загрузки медиафайлов

Кроме основного клиента конфигурация хранит два необязательных: `setHttpClientForUpdates()` — для длинного
опроса событий и `setHttpClientForUploader()` — для загрузки медиафайлов. Если клиент для опроса не задан, опрос
выполняет основной клиент. Если не задан клиент для загрузки, `MaxApiConfig` создаёт для неё отдельный
`CurlHttpClient` методом `makeCurlHttpClient(useSystemCaCertificates: true)`: серверы загрузки используют сертификаты
публичных удостоверяющих центров, поэтому он проверяет их по системным сертификатам, без CA-бандла Минцифры.
Для загрузки через PSR-18 или через прокси задайте клиент явно через `setHttpClientForUploader()`.
Разделение нужно, когда запросам требуются разные таймауты: для `PsrHttpClient` таймаут задают при настройке
используемого клиента PSR-18, поэтому под **long polling** выделяют второй экземпляр с бо́льшим значением.

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use MaxMessenger\Bot\MaxApiConfig;
use MaxMessenger\Bot\PsrHttpClient\PsrHttpClient;

$factory = new HttpFactory();

$config = (new MaxApiConfig('your-access-token'))
    ->setHttpClient(new PsrHttpClient(new Client(['timeout' => 30]), $factory, $factory))
    ->setHttpClientForUpdates(new PsrHttpClient(new Client(['timeout' => 120]), $factory, $factory));
```

Какой клиент выполнит запрос, видно у `MaxHttpClient`: `getHttpClient()`, `getHttpClientForUpdates()`
и `getHttpClientForUploader()`.

### Собственный транспорт

Вместо готовых клиентов можно подключить свой: достаточно реализовать `Contract\Http\HttpClientInterface`
с единственным методом `request(HttpRequestInterface $request): HttpResponseInterface`.
Запрос описывают `HttpRequestInterface` (метод, адрес, заголовки, тело и ограничения времени) и `BodyInterface`
(тело и его `Content-Type`), ответ — `HttpResponseInterface` (код, заголовки, тело).

С запросом связан интерфейс потока:

- `StreamInterface` — тело запроса потоком: `BodyInterface::getBody()` возвращает строку либо поток.
  Его метод `getContents()` возвращает оставшиеся данные от текущей позиции до конца, как в PSR-7.
  `CurlHttpClient` перед отправкой вызывает `seek(0)` и получает строку через этот метод при размере
  не больше `$postDataStringThreshold`, а более крупные потоки читает порциями.

Клиент применяет те ограничения времени, которые умеет; PSR-прослойка игнорирует оба вида ограничений.

`HttpRequestInterface` реализует готовый класс запроса `HttpClient\HttpRequest`,
а поток `PsrHttpClient` оборачивает в PSR-7 классом `PsrHttpClient\PsrStream`.

### Ошибки

Ошибки обмена возникают, если ответ не получен или полученный ответ не подошёл. Первые бросает транспорт,
вторые — `MaxHttpClient`. Отдельно `MaxApiClient` выбрасывает `SimpleQueryError`, когда поле `success` в ответе API
не равно `true`.

При HTTP 200 операция также может завершиться ошибкой: сервер возвращает `success: false`, `code` и `message`.
Для обработки такой ошибки используйте `$exception->getErrorCode()` у `SimpleQueryError`,
а для ошибок с HTTP 4xx — `$exception->getError()?->getCode()` у `HttpException`.
Сравнивайте код API (например, `not.found`), а не текст `message`.
Если ответ не содержит `code`, `SimpleQueryError::getErrorCode()` возвращает `null`.
Метод `SimpleQueryError::getResult()` возвращает исходный объект `SimpleQueryResult`
со всеми данными ответа API, включая дополнительные поля и конкретный тип модели.

```
MaxApiException
├── HttpClient\Exception\HttpClientException            запрос не выполнен, ответа нет
│   ├── TemporaryNetworkException                       временный сбой сети
│   │   └── TimeoutException                            истекло время ожидания
│   └── CurlHttpClient\Exception\CurlException          ошибка curl
├── HttpClient\Exception\HttpResponse\HttpResponseException   ответ получен
│   ├── Http\HttpException                              сервер вернул неожиданный код
│   │   ├── OkException (200), PartialUploadException (201)
│   │   ├── BadRequestException (400), UnauthorizedException (401), ForbiddenException (403)
│   │   ├── NotFoundException (404), NotAllowedException (405), NotAcceptableException (406)
│   │   ├── ProxyAuthenticationRequiredException (407), UnsupportedMediaTypeException (415)
│   │   ├── RangeNotSatisfiableException (416), UnprocessableEntityException (422)
│   │   ├── TooManyRequestsException (429)
│   │   ├── InternalHttpException (500), NotImplementedException (501), BadGatewayException (502)
│   │   ├── ServiceUnavailableException (503), GatewayTimeoutException (504)
│   │   └── UnknownException                            прочие коды
│   ├── ParseDataException → JsonDecodeException, UnexpectedFormatException
│   └── UnexpectedContentTypeException                  сервер вернул не JSON
├── HttpClient\Exception\HttpRequest\HttpRequestException     запрос не отправлен
│   └── JsonEncodeException                             тело запроса не кодируется в JSON
└── Exception\SimpleQueryError                          простая ошибка, о которой API сообщил в ответе
    └── ModifyMembersError                              ошибка изменения списка участников
```

PSR-клиент заворачивает ошибку с распознанным таймаутом в `TimeoutException`, а любую другую сетевую ошибку клиента
PSR-18 (`NetworkExceptionInterface`) — в `TemporaryNetworkException`. Прочие ошибки клиента PSR-18 тоже становятся
`TemporaryNetworkException`, если в их тексте или тексте вложенной ошибки есть код временной ошибки curl
(`cURL error N:`, те же коды, что повторяет `CurlHttpClient`): так Guzzle сообщает, например, об обрыве соединения
во время передачи данных. Остальные ошибки отдаются как есть.

Каждая ошибка обмена хранит запрос (`getRequest()`), а ошибки ответа — ещё и сам ответ (`getResponse()`) и код ответа в
`getCode()`. У `HttpException` есть `getError()`: разобранное описание ошибки API или `null`, если тело ответа разобрать
не удалось — тогда описание остаётся в теле ответа. Ошибки подготовки запроса (`HttpRequestException`) возникают до
отправки, когда запроса ещё нет, поэтому его и не хранят: у `JsonEncodeException` вместо него само тело (`getData()`).

При HTTP 5xx сервер может вернуть ответ без структурированного описания ошибки API.
В этом случае `HttpException::getError()` возвращает `null`;
HTTP-код доступен через `getCode()`, а исходное тело ответа — через `getResponse()->getBody()`.

Временная ошибка транспорта оборачивает исходную: описание и код она берёт у неё, а саму её отдаёт из `getPrevious()`.
У `CurlHttpClient` внутри лежит `CurlException` с кодом curl (константы `CURLE_*`), у `PsrHttpClient` — исключение
клиента PSR-18. Стандарт PSR-18 кодов не описывает: у большинства реализаций там `0`, а у остальных значение своё,
поэтому сравнивать код с кодами curl можно, только зная транспорт.

**Таймаут.** `TimeoutException` наследует `TemporaryNetworkException`, так что `catch (TemporaryNetworkException)` ловит
и его, а поймать отдельно можно там, где таймаут значит не то же самое, что обрыв связи. `CurlHttpClient` бросает его по
коду curl `CURLE_OPERATION_TIMEOUTED` — это и таймаут соединения, и таймаут ответа, и обрыв слишком медленной передачи.
`PsrHttpClient` распознаёт таймаут по тексту ошибки клиента, потому что кода для этого в PSR-18 нет: у клиентов на curl
текст всегда английский и распознаётся, у клиентов на потоках PHP таймаут ответа неотличим от прочих сбоев и приходит
обычной `TemporaryNetworkException`.

**Повторные попытки.** Временные сбои помечены интерфейсом `HttpClient\Exception\TemporaryExceptionInterface`:
это `TemporaryNetworkException` вместе с `TimeoutException` плюс HTTP коды `429`, `500`, `502`, `503` и `504`.
Запросы с такими ошибками `MaxHttpClient` повторяет по расписанию из `MaxApiConfig::setRetryAttempts()`.
Для `POST` можно задать отдельное расписание через `MaxApiConfig::setPostRetryAttempts()`: `null` использует общее
расписание, `[]` отключает транспортные повторы `POST`. Отдельно `sendMessage()` повторяет отправку, когда сервер
отвечает `400` с ошибкой `attachment.not.ready` — вложение ещё не обработано (проверка `Error::isAttachmentNotReady()`).
Расписание для этого случая задаётся через `MaxApiConfig::setAttachmentRetryAttempts()`.

## Методы API

### addMembers

Добавляет участников в групповой чат. Могут потребоваться дополнительные права.
Подписчиков в канал с помощью этого метода добавить нельзя.

**Параметры:**

| Параметр   | Тип                            | Описание                                      |
|------------|--------------------------------|-----------------------------------------------|
| `$chatId`  | `int`                          | ID группового чата.                           |
| `$userIds` | `UserIdsList\|RawModel\|int[]` | Список ID пользователей для добавления в чат. |

Если поле `success` не равно `true`, метод выбрасывает `ModifyMembersError` — наследник `SimpleQueryError`.
В исключении доступны:

- `getFailedUserIds()` — идентификаторы пользователей, которых не удалось добавить в групповой чат или удалить из него.
- `getFailedUserDetails()` — подробное объяснение, почему пользователь не был добавлен в групповой чат.

```php
try {
    $apiClient->addMembers($chatId, $userIds);
} catch (\MaxMessenger\Bot\Exception\ModifyMembersError $exception) {
    error_log($exception->getErrorCode() ?? 'unknown');
    $failedUserIds = $exception->getFailedUserIds();
    $failedUserDetails = $exception->getFailedUserDetails();
}
```

**Документация API:** [POST /chats/-chatId-/members](https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members)

---

### answerOnCallback

Отправляет ответ на Callback после того, как пользователь нажал на кнопку. Ответом является обновлённое сообщение.

**Ограничения:** можно отправлять не более двух ответов в секунду в один диалог, групповой чат или канал.
При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.

**Параметры:**

| Параметр              | Тип                                        | Описание                                                                                                                                                                                                                                                                |
|-----------------------|--------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$callbackId`         | `non-empty-string\|Callback`               | Идентификатор кнопки, по которой пользователь кликнул (pattern: `'^\s*\S[\s\S]*$'`). Бот получает идентификатор как часть `Update` с типом `message_callback`. Можно получить из `$update->getCallback()->getCallbackId()`. Также можно передать сам объект `Callback`. |
| `$answer`             | `CallbackAnswer\|RawModel\|NewMessageBody` | Ответ на callback: обновлённое сообщение.                                                                                                                                                                                                                               |
| `$disableLinkPreview` | `bool`                                     | Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения или поста. По умолчанию: `false`.                                                                                                                                                        |

**Документация API:** [POST /answers](https://dev.max.ru/docs-api/methods/POST/answers)

---

### deleteAdmin

Отменяет права администратора в групповом чате или канале. Псевдоним `deleteAdmins()` — метод вызывает его.

Лишает пользователя или бота прав администратора в групповом чате или канале.
При этом из чата и канала они не исключаются.

Бот должен быть администратором этого чата или канала с правом `add_admins`.

**Параметры:**

| Параметр  | Тип   | Описание                                                                       |
|-----------|-------|--------------------------------------------------------------------------------|
| `$chatId` | `int` | ID группового чата или канала.                                                 |
| `$userId` | `int` | Идентификатор пользователя или бота, которого надо лишить прав администратора. |

**Документация API:** [DELETE /chats/-chatId-/members/admins/-userId-](https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-)

---

### deleteAdmins

Отменяет права администратора в групповом чате или канале.

Лишает пользователя или бота прав администратора в групповом чате или канале.
При этом из чата и канала они не исключаются.

Бот должен быть администратором этого чата или канала с правом `add_admins`.

**Параметры:**

| Параметр  | Тип   | Описание                                                                       |
|-----------|-------|--------------------------------------------------------------------------------|
| `$chatId` | `int` | ID группового чата или канала.                                                 |
| `$userId` | `int` | Идентификатор пользователя или бота, которого надо лишить прав администратора. |

**Документация API:** [DELETE /chats/-chatId-/members/admins/-userId-](https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/admins/-userId-)

---

### deleteChat

> **Устарело.** Метод удалён из официальной схемы API. На 15 сентября 2026 г. он остаётся рабочим,
> но может быть отключён в любой момент.

Удаляет групповой чат для всех участников.

Удалить чат может только его владелец: у бота это чаты, созданные по кнопке создания чата (тип `chat`)
в его сообщении — владельцем такого чата становился сам бот. Такая кнопка удалена из официальной схемы API,
поэтому новые чаты боту создавать нечем. В остальных случаях, даже с правами администратора, сервер
отвечает ошибкой `Insufficient access rights to perform this action`.

**Параметры:**

| Параметр  | Тип   | Описание |
|-----------|-------|----------|
| `$chatId` | `int` | ID чата. |

**Документация API:** [DELETE /chats/-chatId-](https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-)

---

### deleteComment

Удаляет комментарий пользователя или бота к посту в канале. Можно удалять как свои комментарии, так и чужие.
Восстановить удалённый комментарий нельзя.

Если канал архивирован или в нём отключены комментарии, старые комментарии по-прежнему можно удалять.

Для удаления чужих комментариев бот должен быть администратором этого канала с правом `delete`.

**Параметры:**

| Параметр     | Тип                                                    | Описание                                                                                                                                                          |
|--------------|--------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId` | `non-empty-string\|Message\|MessageBody`               | Идентификатор поста (`mid`), комментарий к которому нужно удалить (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |
| `$commentId` | `non-empty-string\|CommentMessage\|CommentMessageBody` | Идентификатор удаляемого комментария (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `CommentMessage` или `CommentMessageBody`.                |

**Документация API:** [DELETE /messages/-messageId-/comments](https://dev.max.ru/docs-api/methods/DELETE/messages/-messageId-/comments)

---

### deleteMessage

Удаляет сообщение в диалоге или групповом чате либо пост в канале.

В диалоге бот может удалять только собственные сообщения. В групповом чате бот может удалять собственные сообщения без
прав администратора; для удаления чужих сообщений требуется право `write`. Для удаления постов в канале требуется
право `delete`.

Можно удалять не более двух сообщений в секунду в одном диалоге, групповом чате или канале.
При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед удалением.

**Параметры:**

| Параметр     | Тип                                      | Описание                                                                                                                                                          |
|--------------|------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId` | `non-empty-string\|Message\|MessageBody` | ID удаляемого сообщения (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody` — ID будет извлечён из него автоматически. |

**Документация API:** [DELETE /messages](https://dev.max.ru/docs-api/methods/DELETE/messages)

---

### editChat

Редактирует информацию о групповом чате или канале, включая название, описание,
иконку и закреплённое сообщение или пост.

Могут потребоваться дополнительные права.

**Параметры:**

| Параметр     | Тип                   | Описание                        |
|--------------|-----------------------|---------------------------------|
| `$chatId`    | `int`                 | ID чата или канала.             |
| `$chatPatch` | `ChatPatch\|RawModel` | Данные для редактирования чата. |

**Возвращает:** `Chat` — Обновлённый объект чата или канала.

**Документация API:** [PATCH /chats/-chatId-](https://dev.max.ru/docs-api/methods/PATCH/chats/-chatId-)

---

### editComment

Редактирует комментарий к посту в канале.

Комментарии, опубликованные от имени канала, можно редактировать, только если боту назначено право администратора
`edit`; без этого права бот может редактировать лишь свои комментарии.

Комментарий можно редактировать только в течение 26 часов после публикации. Позже API возвращает HTTP 422
с кодом `comments.timeout_exceeded` (`UnprocessableEntityException`).
Ограничение действует и для архивированного канала, и при отключённых комментариях.

**Параметры:**

| Параметр       | Тип                                                    | Описание                                                                                                                                                                  |
|----------------|--------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId`   | `non-empty-string\|Message\|MessageBody`               | Идентификатор поста (`mid`), комментарий к которому нужно отредактировать (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |
| `$commentId`   | `non-empty-string\|CommentMessage\|CommentMessageBody` | Идентификатор редактируемого комментария (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `CommentMessage` или `CommentMessageBody`.                    |
| `$commentBody` | `NewCommentBody\|RawModel\|non-empty-string`           | Тело нового комментария.                                                                                                                                                  |

**Документация API:** [PUT /messages/-messageId-/comments](https://dev.max.ru/docs-api/methods/PUT/messages/-messageId-/comments)

---

### editMessage

Редактирует сообщение в диалоге или групповом чате либо пост в канале.

В диалоге и групповом чате бот может редактировать только собственные сообщения без прав администратора.
Для редактирования постов в канале требуется право администратора `edit`.

Ограничения:

- Есть особенности редактирования сообщений в диалогах с ботом, связанные со сроком давности:
    - сообщения с кнопками `inline_keyboard` редактируются независимо от срока давности.
    - остальные сообщения редактируются, если они отправлены менее 7 суток назад.
- В групповых чатах сообщения, а в каналах посты редактируются независимо от срока давности.
- Можно редактировать не более двух сообщений в секунду в одном диалоге, групповом чате или канале.
  При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед редактированием.

**Параметры:**

| Параметр       | Тип                                          | Описание                                                                                                                                                              |
|----------------|----------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId`   | `non-empty-string\|Message\|MessageBody`     | ID редактируемого сообщения (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody` — ID будет извлечён из него автоматически. |
| `$messageBody` | `NewMessageBody\|RawModel\|non-empty-string` | Тело нового сообщения.                                                                                                                                                |

**Документация API:** [PUT /messages](https://dev.max.ru/docs-api/methods/PUT/messages)

---

### editMyCommands

Добавляет, изменяет или удаляет команды бота. Подсказки при вводе `/` отображаются в диалогах на iOS, Android
и десктопе, а в групповых чатах — на iOS и Android. На iOS в диалоге список также открывается кнопкой `/`.
В каналах команды не отображаются.

Чтобы удалить команды, передайте пустой массив `commands`.

Метод заменяет весь список команд целиком: команды, которых нет в переданном списке, будут удалены.

**Параметры:**

| Параметр    | Тип                          | Описание                                          |
|-------------|------------------------------|---------------------------------------------------|
| `$commands` | `BotCommandsPatch\|RawModel` | Данные для обновления команд бота (maxItems: 32). |

**Возвращает:** `BotCommandsInfo` — Информация о командах бота.

**Пример:**

```php
use MaxMessenger\Bot\Model\Request\BotCommand;
use MaxMessenger\Bot\Model\Request\BotCommandsPatch;

$commandsInfo = $apiClient->editMyCommands(BotCommandsPatch::make([
    new BotCommand('start', 'Начать работу с ботом'),
    new BotCommand('help', 'Получить справку'),
]));

// Удаление всех команд бота
$apiClient->editMyCommands(BotCommandsPatch::make([]));
```

**Документация API:** [PATCH /me/commands](https://dev.max.ru/docs-api/methods/PATCH/me/commands)

---

### getAdmins

Получает список администраторов группового чата или канала. Бот должен быть администратором этого чата или канала.

**Параметры:**

| Параметр  | Тип   | Описание |
|-----------|-------|----------|
| `$chatId` | `int` | ID чата. |

**Возвращает:** `ChatMembersList` — Список администраторов.

**Документация API:** [GET /chats/-chatId-/members/admins](https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins)

---

### getChat

Получает информацию о чате по его ID.

**Параметры:**

| Параметр  | Тип   | Описание                |
|-----------|-------|-------------------------|
| `$chatId` | `int` | ID запрашиваемого чата. |

**Возвращает:** `Chat` — Информация о чате.

**Документация API:** [GET /chats/-chatId-](https://dev.max.ru/docs-api/methods/GET/chats/-chatId-)

---

### getCommentById

Возвращает информацию о комментарии к посту в канале по его идентификатору (`mid`).

Бот должен быть администратором этого канала с правом `read_all_messages`.

**Параметры:**

| Параметр     | Тип                                                    | Описание                                                                                                                                                      |
|--------------|--------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId` | `non-empty-string\|Message\|MessageBody`               | Идентификатор поста (`mid`), к которому относится комментарий (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |
| `$commentId` | `non-empty-string\|CommentMessage\|CommentMessageBody` | Идентификатор комментария (`mid`) (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `CommentMessage` или `CommentMessageBody`.               |

**Возвращает:** `CommentMessage` — Информация о комментарии, идентификатор которого был передан в запросе.

**Исключения:** `NotFoundException` — Если комментарий или пост не найдены, либо у бота нет к ним доступа.

**Документация API:** [GET /messages/-messageId-/comments/-commentId-](https://dev.max.ru/docs-api/methods/GET/messages/-messageId-/comments/-commentId-)

---

### getComments

Возвращает комментарии к посту в канале. Можно указать промежуток времени, за который нужно получить комментарии,
и/или их количество. Возвращаются последние `$count` комментариев из указанного промежутка времени.

Если указан `$commentIds`, возвращаются только запрошенные комментарии, а остальные параметры игнорируются.

Бот должен быть администратором этого канала с правом `read_all_messages`.

**Параметры:**

| Параметр      | Тип                                       | Описание                                                                                                                                                      |
|---------------|-------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId`  | `non-empty-string\|Message\|MessageBody`  | Идентификатор поста (`mid`), к которому относятся комментарии (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |
| `$commentIds` | `non-empty-array<non-empty-string>\|null` | Список ID комментариев, которые нужно получить (uniqueItems: true). Если параметр указан, остальные параметры игнорируются.                                   |
| `$before`     | `non-negative-int\|null`                  | Время, до которого будут запрошены все комментарии с начала поста (Unix-время в миллисекундах).                                                               |
| `$after`      | `non-negative-int\|null`                  | Время, начиная с которого будут запрошены все комментарии до конца поста (Unix-время в миллисекундах).                                                        |
| `$count`      | `int<1, 100>`                             | Максимальное количество комментариев в ответе. По умолчанию: `50`.                                                                                            |

**Возвращает:** `CommentMessageList` — Список запрошенных комментариев.

**Документация API:** [GET /messages/-messageId-/comments](https://dev.max.ru/docs-api/methods/GET/messages/-messageId-/comments)

---

### getCommentsById

Получение комментариев по ID. Возвращает массив заданных комментариев к посту в канале.
Можно указать один идентификатор или несколько.

Бот должен быть администратором этого канала с правом `read_all_messages`.

**Параметры:**

| Параметр      | Тип                                      | Описание                                                                                                                                                      |
|---------------|------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId`  | `non-empty-string\|Message\|MessageBody` | Идентификатор поста (`mid`), к которому относятся комментарии (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |
| `$commentIds` | `non-empty-array<non-empty-string>`      | Список ID комментариев, которые нужно получить (uniqueItems: true).                                                                                           |

**Возвращает:** `CommentMessageList` — Список запрошенных комментариев.

---

### getCommentsFromPost

Получение комментариев к посту в канале. Возвращает последние `$count` комментариев к указанному посту.
Можно указать промежуток времени, за который нужно получить комментарии.

Бот должен быть администратором этого канала с правом `read_all_messages`.

**Параметры:**

| Параметр     | Тип                                      | Описание                                                                                                                                                      |
|--------------|------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId` | `non-empty-string\|Message\|MessageBody` | Идентификатор поста (`mid`), к которому относятся комментарии (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |
| `$before`    | `non-negative-int\|null`                 | Время, до которого будут запрошены все комментарии с начала поста (Unix-время в миллисекундах).                                                               |
| `$after`     | `non-negative-int\|null`                 | Время, начиная с которого будут запрошены все комментарии до конца поста (Unix-время в миллисекундах).                                                        |
| `$count`     | `int<1, 100>`                            | Максимальное количество комментариев в ответе. По умолчанию: `50`.                                                                                            |

**Возвращает:** `CommentMessageList` — Список запрошенных комментариев.

---

### getMaxHttpClient

HTTP-клиент для прямых запросов к Max Messenger API. Для использования необходимо знать формат запросов и ответов API.

Сам транспорт (`CurlHttpClient` или `PsrHttpClient`) доступен уровнем ниже —
`$client->getMaxHttpClient()->getHttpClient()`.

**Возвращает:** `MaxHttpClientInterface`

---

### getMembers

Получает участников группового чата или канала. Бот должен быть администратором этого чата или канала.

**Параметры:**

| Параметр   | Тип                          | Описание                                                                                                                                         |
|------------|------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `$chatId`  | `int`                        | ID чата.                                                                                                                                         |
| `$userIds` | `non-empty-array<int>\|null` | Список ID пользователей для получения их членства (uniqueItems: true). Когда этот аргумент передан, аргументы `$count` и `$marker` игнорируются. |
| `$marker`  | `int\|null`                  | Указатель на следующую страницу данных.                                                                                                          |
| `$count`   | `int<1, 100>`                | Максимальное количество участников в ответе. По умолчанию: `20`.                                                                                 |

**Возвращает:** `ChatMembersList` — Возвращает список участников и указатель на следующую страницу данных.

**Документация API:** [GET /chats/-chatId-/members](https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members)

---

### getMembership

Получает информацию о членстве бота в групповом чате. Возвращает информацию о членстве текущего бота в групповом чате.
Бот идентифицируется с помощью токена доступа.

**Параметры:**

| Параметр  | Тип   | Описание |
|-----------|-------|----------|
| `$chatId` | `int` | ID чата. |

**Возвращает:** `ChatMember` — Текущая информация о членстве бота.

**Документация API:** [GET /chats/-chatId-/members/me](https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/me)

---

### getMessageById

Получает сообщение по ID. Возвращает сообщение по его ID.

**Параметры:**

| Параметр     | Тип                                      | Описание                                                                                                                                                   |
|--------------|------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId` | `non-empty-string\|Message\|MessageBody` | ID сообщения (`mid`), чтобы получить одно сообщение в чате (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |

**Возвращает:** `Message` — Возвращает одно сообщение.

**Документация API:** [GET /messages/-messageId-](https://dev.max.ru/docs-api/methods/GET/messages/-messageId-)

---

### getMessages

Получает список сообщений или постов.
Возвращает массив сообщений из чата, постов из канала или указанного списка сообщений.

Для выполнения запроса нужно указать один из параметров — `chat_id` или `message_ids`:

- `chat_id` — ID чата для получения сообщений из указанного чата. Сообщения возвращаются в обратном порядке:
  последние сообщения будут первыми в массиве
- `message_ids` — Список ID сообщений (`mid`). Можно указать один идентификатор или несколько

**Параметры:**

| Параметр      | Тип                                       | Описание                                                                                                          |
|---------------|-------------------------------------------|-------------------------------------------------------------------------------------------------------------------|
| `$messageIds` | `non-empty-array<non-empty-string>\|null` | Список ID сообщений, которые нужно получить (uniqueItems: true). Обязательный параметр, если не указан `$chatId`. |
| `$chatId`     | `int\|null`                               | ID чата, чтобы получить сообщения из определённого чата. Обязательный параметр, если не указан `$messageIds`.     |
| `$before`     | `non-negative-int\|null`                  | Запросить сообщения или посты до указанного времени (Unix-время в миллисекундах).                                 |
| `$after`      | `non-negative-int\|null`                  | Запросить сообщения или посты после указанного времени (Unix-время в миллисекундах).                              |
| `$count`      | `int<1, 100>`                             | Максимальное количество сообщений в ответе. По умолчанию: `50`.                                                   |

**Возвращает:** `MessageList` — Возвращает список сообщений.

**Документация API:** [GET /messages](https://dev.max.ru/docs-api/methods/GET/messages)

---

### getMessagesById

Получение сообщений по ID. Возвращает информацию о запрошенных сообщениях.
Можно указать один идентификатор или несколько.

**Параметры:**

| Параметр      | Тип                                 | Описание                                                         |
|---------------|-------------------------------------|------------------------------------------------------------------|
| `$messageIds` | `non-empty-array<non-empty-string>` | Список ID сообщений, которые нужно получить (uniqueItems: true). |

**Возвращает:** `MessageList` — Возвращает список сообщений.

---

### getMessagesFromChat

Получение сообщений из чата. Возвращает массив сообщений из указанного чата.
Сообщения возвращаются в обратном порядке: последние сообщения будут первыми в массиве.

**Параметры:**

| Параметр  | Тип                      | Описание                                                                             |
|-----------|--------------------------|--------------------------------------------------------------------------------------|
| `$chatId` | `int`                    | ID чата, чтобы получить сообщения из определённого чата.                             |
| `$before` | `non-negative-int\|null` | Запросить сообщения или посты до указанного времени (Unix-время в миллисекундах).    |
| `$after`  | `non-negative-int\|null` | Запросить сообщения или посты после указанного времени (Unix-время в миллисекундах). |
| `$count`  | `int<1, 100>`            | Максимальное количество сообщений в ответе. По умолчанию: `50`.                      |

**Возвращает:** `MessageList` — Возвращает список сообщений.

---

### getMyInfo

Получает информацию о боте. Возвращает информацию о боте, от имени которого выполняется запрос.

**Возвращает:** `BotInfo` — Информация о боте.

**Документация API:** [GET /me](https://dev.max.ru/docs-api/methods/GET/me)

---

### getPinnedMessage

Получает закреплённое сообщение в групповом чате или пост в канале.

Бот должен быть администратором этого чата или канала с правом `read_all_messages`.

**Параметры:**

| Параметр  | Тип   | Описание            |
|-----------|-------|---------------------|
| `$chatId` | `int` | ID чата или канала. |

**Возвращает:** `GetPinnedMessageResult` — Закреплённое сообщение или пост.

**Документация API:** [GET /chats/-chatId-/pin](https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/pin)

---

### getSubscriptions

Получает подписки. Возвращает список всех подписок данного бота.

**Возвращает:** `GetSubscriptionsResult` — Список подписок.

**Документация API:** [GET /subscriptions](https://dev.max.ru/docs-api/methods/GET/subscriptions)

---

### getUpdates

Получает обновления. Выполняет долгий запрос (long polling). Каждое обновление имеет свой номер последовательности.
Свойство `marker` в ответе указывает на следующее ожидаемое обновление.

Если параметр `marker` **не передан**, бот получит все ранее не полученные обновления.

> Этот метод можно использовать для получения событий при разработке и тестировании, если ваш бот не подписан на
> Webhook. Для production-окружения рекомендуем использовать Webhook.

**Параметры:**

| Параметр   | Тип                               | Описание                                                                                                             |
|------------|-----------------------------------|----------------------------------------------------------------------------------------------------------------------|
| `$limit`   | `int<1, 1000>`                    | Максимальное количество событий для получения. По умолчанию: `100`.                                                  |
| `$timeout` | `int<0, 90>`                      | Тайм-аут в секундах для долгого опроса. По умолчанию: `30`.                                                          |
| `$marker`  | `int\|null`                       | Маркер для получения событий с конкретной позиции. Для получения всех ранее непрочитанных событий, передайте `null`. |
| `$types`   | `array<UpdateType\|string>\|null` | Список типов событий, которые бот хочет получить (uniqueItems: true).                                                |

**Возвращает:** `UpdateList` — Список событий.

**Документация API:** [GET /updates](https://dev.max.ru/docs-api/methods/GET/updates)

---

### getUploadUrl

Получает URL для загрузки медиафайлов. Возвращает URL для последующей загрузки медиафайла.

**Параметры:**

| Параметр | Тип          | Описание                |
|----------|--------------|-------------------------|
| `$type`  | `UploadType` | Тип загружаемого файла. |

**Возвращает:** `UploadEndpoint` — Возвращает URL для загрузки вложения и токен для загрузки медиафайла.

**Документация API:** [POST /uploads](https://dev.max.ru/docs-api/methods/POST/uploads)

---

### getVideoAttachmentDetails

Получает информацию о видео.
Возвращает подробную информацию о прикреплённом видео (URL-адреса воспроизведения и дополнительные метаданные).

**Параметры:**

| Параметр      | Тип                | Описание                                                 |
|---------------|--------------------|----------------------------------------------------------|
| `$videoToken` | `non-empty-string` | Токен видео-вложения (pattern: `'^vid\.[\x21-\x7E]+$'`). |

**Возвращает:** `VideoAttachmentDetails` — Подробная информация о видео.

**Документация API:** [GET /videos/-videoToken-](https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-)

---

### leaveChat

Удаляет бота из группового чата. Удаляет бота из участников группового чата.

**Параметры:**

| Параметр  | Тип   | Описание |
|-----------|-------|----------|
| `$chatId` | `int` | ID чата. |

**Документация API:** [DELETE /chats/-chatId-/members/me](https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members/me)

---

### pinMessage

Закрепляет сообщение в групповом чате или пост в канале.
Бот должен быть администратором этого чата или канала с правом `pin_message`.

**Параметры:**

| Параметр      | Тип                                          | Описание                                                               |
|---------------|----------------------------------------------|------------------------------------------------------------------------|
| `$chatId`     | `int`                                        | ID группового чата или канала, где нужно закрепить сообщение или пост. |
| `$pinMessage` | `PinMessageBody\|RawModel\|non-empty-string` | Сообщение или пост для закрепления.                                    |

**Документация API:** [PUT /chats/-chatId-/pin](https://dev.max.ru/docs-api/methods/PUT/chats/-chatId-/pin)

---

### postAdmins

Назначает администраторов группового чата или канала.

Бот должен быть администратором этого чата или канала с правом `add_admins`.

**Параметры:**

| Параметр  | Тип                                     | Описание                       |
|-----------|-----------------------------------------|--------------------------------|
| `$chatId` | `int`                                   | ID группового чата или канала. |
| `$admins` | `ChatAdminsList\|RawModel\|ChatAdmin[]` | Список администраторов.        |

**Документация API:** [POST /chats/-chatId-/members/admins](https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/admins)

---

### removeMember

Удаляет участника из группового чата или канала.
Бот должен быть администратором этого чата или канала с правом `add_remove_members`.

**Параметры:**

| Параметр  | Тип    | Описание                                                                                                                                                                                    |
|-----------|--------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$chatId` | `int`  | ID чата.                                                                                                                                                                                    |
| `$userId` | `int`  | Идентификатор пользователя для удаления из чата.                                                                                                                                            |
| `$block`  | `bool` | Если установлено в `true`, пользователь будет заблокирован в чате. Применяется только для чатов с публичной или приватной ссылкой. Игнорируется в остальных случаях. По умолчанию: `false`. |

**Документация API:** [DELETE /chats/-chatId-/members](https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/members)

---

### sendAction

Отправляет действие бота в диалог или групповой чат.

Отправляет в диалог или групповой чат такие действия бота, как например: «набор текста» или «отправка фото».

Для каналов сервер принимает запрос, но участникам действие не показывается.

**Параметры:**

| Параметр  | Тип                                         | Описание                        |
|-----------|---------------------------------------------|---------------------------------|
| `$chatId` | `int`                                       | ID диалога или группового чата. |
| `$action` | `ActionRequestBody\|RawModel\|SenderAction` | Действие бота.                  |

**Документация API:** [POST /chats/-chatId-/actions](https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/actions)

---

### sendComment

Отправляет комментарий к посту в канале. Возвращает созданный комментарий.

Для отправки комментария в настройках канала должны быть включены комментарии, а бот должен быть администратором
этого канала с правом `write`.

**Параметры:**

| Параметр       | Тип                                          | Описание                                                                                                                                                      |
|----------------|----------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$messageId`   | `non-empty-string\|Message\|MessageBody`     | Идентификатор поста (`mid`), к которому относится комментарий (pattern: `'^mid\.[a-zA-Z0-9_\-]+$'`). Также можно передать объект `Message` или `MessageBody`. |
| `$commentBody` | `NewCommentBody\|RawModel\|non-empty-string` | Тело нового комментария.                                                                                                                                      |

**Возвращает:** `SendCommentResult` — Информация о созданном комментарии.

**Документация API:** [POST /messages/-messageId-/comments](https://dev.max.ru/docs-api/methods/POST/messages/-messageId-/comments)

---

### sendMessage

Отправляет сообщение в диалог или групповой чат либо пост в канал. Возвращает созданное сообщение или пост.
Для отправки поста в канал бот должен быть администратором с правом `write`.

**Ограничения:** можно отправлять не более двух сообщений в секунду в один диалог, групповой чат или канал.
При превышении этого лимита сообщения следует ставить в очередь или делать задержку перед отправкой.

Автоматически обрабатывает ошибки готовности вложений с повторными попытками (retry attempts).

**Параметры:**

| Параметр              | Тип                                          | Описание                                                                                               |
|-----------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------|
| `$userId`             | `int\|null`                                  | Если вы хотите отправить сообщение пользователю, укажите его ID.                                       |
| `$chatId`             | `int\|null`                                  | Если сообщение отправляется в чат, укажите его ID.                                                     |
| `$messageBody`        | `NewMessageBody\|RawModel\|non-empty-string` | Тело нового сообщения.                                                                                 |
| `$disableLinkPreview` | `bool`                                       | Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения. По умолчанию: `false`. |

**Возвращает:** `SendMessageResult` — Информация о созданном сообщении.

**Исключения:**

- `RequiredArgumentsException` — Если переданы одновременно `$userId` и `$chatId`.
- `RequiredArgumentException` — Если не передан ни `$userId`, ни `$chatId`.

**Документация API:** [POST /messages](https://dev.max.ru/docs-api/methods/POST/messages)

---

### sendMessageToChat

Отправляет сообщение в групповой чат или пост в канал.
Для отправки поста в канал бот должен быть администратором с правом `write`.

**Параметры:**

| Параметр              | Тип                                          | Описание                                                                                               |
|-----------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------|
| `$chatId`             | `int`                                        | ID чата.                                                                                               |
| `$messageBody`        | `NewMessageBody\|RawModel\|non-empty-string` | Тело нового сообщения.                                                                                 |
| `$disableLinkPreview` | `bool`                                       | Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения. По умолчанию: `false`. |

**Возвращает:** `SendMessageResult` — Информация о созданном сообщении.

---

### sendMessageToUser

Отправить сообщение пользователю. Отправляет сообщение в диалог.
В результате метода возвращается идентификатор нового сообщения.

**Параметры:**

| Параметр              | Тип                                          | Описание                                                                                               |
|-----------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------|
| `$userId`             | `int`                                        | ID пользователя.                                                                                       |
| `$messageBody`        | `NewMessageBody\|RawModel\|non-empty-string` | Тело нового сообщения.                                                                                 |
| `$disableLinkPreview` | `bool`                                       | Если `true`, сервер не будет генерировать превью для ссылок в тексте сообщения. По умолчанию: `false`. |

**Возвращает:** `SendMessageResult` — Информация о созданном сообщении.

---

### subscribe

Подписывает на обновления. Настраивает доставку событий бота через Webhook — основной механизм получения событий в
продуктовых интеграциях. При активной подписке Long Polling не работает.

После вызова метода события отправляются на указанный Webhook-endpoint в виде HTTPS POST-запросов с объектом `Update`.

> Webhook-endpoint должен возвращать **HTTP 200** в течение 30 секунд.\
> Любой другой код ответа или превышение тайм-аута — ошибка доставки.

**Параметры:**

| Параметр        | Тип                                 | Описание            |
|-----------------|-------------------------------------|---------------------|
| `$subscription` | `SubscriptionRequestBody\|RawModel` | Параметры подписки. |

**Документация API:** [POST /subscriptions](https://dev.max.ru/docs-api/methods/POST/subscriptions)

---

### unpinMessage

Открепляет сообщение в групповом чате или пост в канале.
Бот должен быть администратором этого чата или канала с правом `pin_message`.

**Параметры:**

| Параметр  | Тип   | Описание                                                                     |
|-----------|-------|------------------------------------------------------------------------------|
| `$chatId` | `int` | ID группового чата или канала, в котором нужно открепить сообщение или пост. |

**Документация API:** [DELETE /chats/-chatId-/pin](https://dev.max.ru/docs-api/methods/DELETE/chats/-chatId-/pin)

---

### unsubscribe

Отписывает от обновлений. Отписывает бота от получения событий через Webhook. После вызова этого метода бот перестаёт
получать уведомления о новых событиях, и становится доступна доставка уведомлений через API с длительным опросом.

**Параметры:**

| Параметр | Тип                | Описание                                           |
|----------|--------------------|----------------------------------------------------|
| `$url`   | `non-empty-string` | URL, который нужно удалить из подписок на WebHook. |

**Документация API:** [DELETE /subscriptions](https://dev.max.ru/docs-api/methods/DELETE/subscriptions)

---

## Вспомогательные методы

### validateContactAttachmentHash

Проверяет соответствие хеша и значения vcfInfo. Позволяет проверить, что пользователь поделился номером телефона,
привязанным к его профилю в МАКС.

Метод принимает подпись в hex или base64 (включая URL-safe вариант, с padding и без).
При проверке учитываются исходные, экранированные и нормализованные переносы строк в `vcf_info`.
Отсутствующая или неверная подпись возвращает `false`.

Если проверка прошла успешно, Вы можете получить номер телефона следующим способом:

```php
$phones = $payload->getPhones();
```

**Параметры:**

| Параметр   | Тип                        | Описание                      |
|------------|----------------------------|-------------------------------|
| `$payload` | `ContactAttachmentPayload` | Данные контакта для проверки. |

**Возвращает:** `bool` — `true` если хеш соответствует, `false` в противном случае.

**См. также:** `MessageCreatedEvent::isSelfContact`
