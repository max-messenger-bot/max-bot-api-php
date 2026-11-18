# Обработка нажатий кнопок

## Введение

Callback-события — это события нажатия пользователем callback-кнопки во встроенной клавиатуре сообщения.
Обработчик получает объект `MessageCallbackEvent`, имя действия и payload.

Для обработки действий используются классы [CallbackHandler](../src/MaxBot/CallbackHandler.php)
и [CallbackJsonHandler](../src/MaxBot/CallbackJsonHandler.php), а также методы `MaxBot`:

- `onMessageCallback()` — регистрация обработчика события нажатия кнопки;
- `getCallbackHandler()` — получение основного обработчика действий кнопок с текстовым payload;
- `getCallbackJsonHandler()` — получение основного обработчика действий кнопок с JSON-payload;
- `onCallback()` и `onCallbackJson()` — регистрация отдельного действия.

Связанная документация:

- [Примеры обработки нажатий кнопок](Examples/ProcessingCallbacks.md)
- [Примеры обработки событий бота](Examples/ProcessingEvents.md)
- [Обработка событий бота](ProcessingEvents.md)
- [API-клиент](ApiClient.md)

## Получение обработчиков

Метод `getCallbackHandler()` возвращает основной обработчик `CallbackHandler`.
При первом вызове он создаёт и регистрирует его через `onMessageCallback()`,
сохраняя экземпляр для последующих вызовов `getCallbackHandler()` и `onCallback()`.

Метод `getCallbackJsonHandler()` возвращает основной обработчик `CallbackJsonHandler`.
При первом вызове он создаёт и регистрирует его через `onMessageCallback()`,
сохраняя экземпляр для последующих вызовов `getCallbackJsonHandler()` и `onCallbackJson()`.

Настройки основных обработчиков изменяются через их сеттеры.

### CallbackHandler

`CallbackHandler` обрабатывает текстовые действия.
Разделитель имени действия и payload можно настроить через `setActionSeparator()`.

```php
use MaxMessenger\Bot\MaxBot;

$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);

$callbackHandler = $bot->getCallbackHandler()->setActionSeparator(':');
```

Отдельный обработчик создайте и зарегистрируйте через `onMessageCallback()`:

```php
use MaxMessenger\Bot\MaxBot\CallbackHandler;

$callbackHandler = new CallbackHandler(actionSeparator: ':');
$bot->onMessageCallback($callbackHandler->handle(...));
```

### CallbackJsonHandler

`CallbackJsonHandler` обрабатывает JSON-payload. Ключ JSON-поля с именем действия по умолчанию — `action`.
Для изменения ключа используйте `setActionKey()`.

```php
$callbackJsonHandler = $bot->getCallbackJsonHandler()->setActionKey('cmd');
```

Отдельный обработчик создайте и зарегистрируйте через `onMessageCallback()`:

```php
use MaxMessenger\Bot\MaxBot\CallbackJsonHandler;

$callbackJsonHandler = new CallbackJsonHandler(actionKey: 'cmd');
$bot->onMessageCallback($callbackJsonHandler->handle(...));
```

## Регистрация обработчиков

Методы `MaxBot::onCallback()` и `MaxBot::onCallbackJson()` используют текущий обработчик своего типа
и возвращают `MaxBot`. Методы `CallbackHandler::onAction()` и `CallbackJsonHandler::onAction()`
регистрируют действия напрямую и возвращают объект обработчика.

| Метод                             | Параметры                                      | Возвращает |
|-----------------------------------|------------------------------------------------|------------|
| `MaxBot::onCallback()`            | `$name: non-empty-string`, `$handler: Closure` | `$this`    |
| `MaxBot::onCallbackJson()`        | `$name: non-empty-string`, `$handler: Closure` | `$this`    |
| `CallbackHandler::onAction()`     | `$name: non-empty-string`, `$handler: Closure` | `$this`    |
| `CallbackJsonHandler::onAction()` | `$name: non-empty-string`, `$handler: Closure` | `$this`    |

Имя действия `$name` должно совпадать с именем в текстовом payload или значением выбранного JSON-поля.

### Текстовые действия

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

$bot->onCallback('buy', static function (MessageCallbackEvent $event, string $action, ?string $payload): void {
    // Для `buy:product_123`: $action === 'buy', $payload === 'product_123'.
});
```

Регистрация напрямую через `CallbackHandler` использует ту же сигнатуру:

```php
$callbackHandler->onAction('buy', static function (
    MessageCallbackEvent $event,
    string $action,
    ?string $payload,
): void {
    // Обработка действия с текстовыми данными.
});
```

### JSON-действия

```php
$bot->onCallbackJson('buy', static function (MessageCallbackEvent $event, string $action, array $payload): void {
    // Для `{"action":"buy","productId":123}`: $action === 'buy', $payload['productId'] === 123.
});
```

Регистрация напрямую через `CallbackJsonHandler` использует ту же сигнатуру:

```php
$callbackJsonHandler->onAction('buy', static function (
    MessageCallbackEvent $event,
    string $action,
    array $payload,
): void {
    // Обработка действия с данными из JSON.
});
```

## Аргументы обработчика

Каждый обработчик получает три аргумента в следующем порядке:

| Аргумент   | Тип                                         | Описание                        |
|------------|---------------------------------------------|---------------------------------|
| `$event`   | `MessageCallbackEvent`                      | Событие нажатия callback-кнопки |
| `$action`  | `string`                                    | Имя действия                    |
| `$payload` | `string\|null` для текста, `array` для JSON | Дополнительные данные действия  |

Для текстовых действий payload содержит текст после разделителя. Если разделителя нет в настройках или
в данных кнопки, payload равен `null`. Пустая строка после разделителя передаётся как `''`.

Для JSON-действий payload содержит весь декодированный массив, включая поле с именем действия.

### Доступ через `userData`

Для доступа к тем же значениям из других обработчиков события они также записываются в `userData`:

| Ключ                            | Тип                   | Описание         |
|---------------------------------|-----------------------|------------------|
| `$event->userData['__action']`  | `string`              | Имя действия     |
| `$event->userData['__payload']` | `string\|array\|null` | Payload действия |

## Настройка обработчиков

Параметры существующего обработчика изменяются его методами `set*()`. Методы `get*()` возвращают текущие настройки.

### CallbackHandler

| Метод                  | Параметры                                  | Возвращает               |
|------------------------|--------------------------------------------|--------------------------|
| `getActionSeparator()` | —                                          | `non-empty-string\|null` |
| `setActionSeparator()` | `$actionSeparator: non-empty-string\|null` | `$this`                  |

```php
$callbackHandler->setActionSeparator(':');

// Для кнопки с payload "buy:product_123" обработчик получит:
// $action === 'buy', $payload === 'product_123'.
```

### CallbackJsonHandler

| Метод            | Параметры                      | Возвращает         |
|------------------|--------------------------------|--------------------|
| `getActionKey()` | —                              | `non-empty-string` |
| `setActionKey()` | `$actionKey: non-empty-string` | `$this`            |

```php
$callbackJsonHandler->setActionKey('cmd');

// Для кнопки с payload {"cmd":"buy","productId":123} обработчик получит:
// $action === 'buy', $payload === ['cmd' => 'buy', 'productId' => 123].
```

## Особенности обработки

### Порядок обработки

Обработчики callback-события вызываются в порядке регистрации. Внутри `CallbackHandler` или `CallbackJsonHandler`
вызываются обработчики извлечённого имени действия, также в порядке регистрации.

Текстовый обработчик пропускает payload, начинающийся с `{`.
JSON-обработчик пропускает payload, который не является JSON-объектом или не содержит непустое строковое имя действия.
Поэтому оба типа обработчиков можно зарегистрировать для одного бота.

### Статус события

Обработчик может вернуть `true`, чтобы отметить событие обработанным и остановить дальнейший обход,
или `false`, чтобы продолжить обработку. При завершении без `return` событие по умолчанию считается обработанным.
Если обработчик сам изменил статус через методы события, используется установленное им значение.

Подробнее — в [обработке событий бота](ProcessingEvents.md) и [обработке ошибок](HandlingErrorsInEventHandlers.md).

### Ограничения

- Разделитель текстового действия по умолчанию не задан.
- Имя JSON-действия должно быть непустой строкой.
