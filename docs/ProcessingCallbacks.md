# Обработка нажатий кнопок

## Введение

**Callback-события** возникают при нажатии пользователями интерактивных кнопок в сообщениях бота. Эти кнопки могут быть
добавлены к сообщениям с использованием различных типов кнопок (callback, keyboard и др.).

Для упрощения обработки Callback-событий (нажатий кнопок) написаны отдельные вспомогательные
классы [CallbackHandler](../src/MaxBot/CallbackHandler.php)
и [CallbackJsonHandler](../src/MaxBot/CallbackJsonHandler.php), а также реализованы методы
`MaxBot::addCallbackHandler()` и `MaxBot::addCallbackJsonHandler()`.

**Связанная документация:**

- Примеры [обработки нажатий кнопок](Examples/ProcessingCallbacks.md)
- Примеры [обработки событий](Examples/ProcessingEvents.md)
- [Обработка событий](ProcessingEvents.md)

## CallbackHandler

Класс `CallbackHandler` предназначен для обработки простых callback-действий с текстовой полезной нагрузкой.

### Получение через MaxBot

Вы можете самостоятельно создать объект класса `CallbackHandler` и зарегистрировать его как получатель событий «новый
Callback». Но также Вы можете воспользоваться методом `MaxBot::addCallbackHandler()`. Данный метод создаёт новый
экземпляр класса и регистрирует его обработчиком события «новый Callback`. При повторном вызове будет возвращён новый
экземпляр.

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$bot = new MaxBot('your-access-token');

// Создание обработчика с разделителем действия и данных
$callbackHandler = $bot->addCallbackHandler(actionSeparator: ' ', actionMaxLength: 128);

// Регистрация обработчика конкретного действия
$callbackHandler->onAction('buy', function (MessageCallbackEvent $event): bool {
    $action = $event->userData['__action'];     // 'buy'
    $payload = $event->userData['__payload'];   // Данные после разделителя

    // Обработка действия...

    return true; // Отмечаем событие как обработанное
});
```

### Методы CallbackHandler

#### onAction

Регистрирует обработчик для конкретного действия.

**Параметры:**

| Параметр   | Тип                                           | Описание             |
|------------|-----------------------------------------------|----------------------|
| `$name`    | `non-empty-string`                            | Имя действия.        |
| `$handler` | `Closure(MessageCallbackEvent): (bool\|void)` | Обработчик действия. |

**Возвращает:** `$this`

#### setActionSeparator

Устанавливает разделитель между действием и полезной нагрузкой.

**Параметры:**

| Параметр           | Тип                      | Описание                       |
|--------------------|--------------------------|--------------------------------|
| `$actionSeparator` | `non-empty-string\|null` | Разделитель действия и данных. |

**Возвращает:** `$this`

#### setActionMaxLength

Устанавливает максимальную длину имени действия.

**Параметры:**

| Параметр           | Тип            | Описание                                                          |
|--------------------|----------------|-------------------------------------------------------------------|
| `$actionMaxLength` | `positive-int` | Максимальная длина действия в символах UTF-8. По умолчанию: `64`. |

**Возвращает:** `$this`

#### getActionSeparator

Возвращает текущий разделитель действий.

**Возвращает:** `non-empty-string\|null`

#### getActionMaxLength

Возвращает текущую максимальную длину действия.

**Возвращает:** `positive-int`

## CallbackJsonHandler

Класс `CallbackJsonHandler` предназначен для обработки callback-действий с полезной нагрузкой в формате JSON.

### Получение через MaxBot

Вы можете самостоятельно создать объект класса `CallbackJsonHandler` и зарегистрировать его как получатель событий
«новый Callback». Но также Вы можете воспользоваться методом `MaxBot::addCallbackJsonHandler()`. Данный метод создаёт
новый экземпляр класса и регистрирует его обработчиком события «новый Callback». При повторном вызове будет возвращён
новый экземпляр.

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$bot = new MaxBot('your-access-token');

// Создание обработчика с JSON-payload
// Ожидается payload вида: {"action": "buy", "productId": 123}
$callbackHandler = $bot->addCallbackJsonHandler(actionKey: 'action');

$callbackHandler->onAction('buy', function (MessageCallbackEvent $event): bool {
    $action = $event->userData['__action'];     // 'buy'
    $payload = $event->userData['__payload'];   // ['action' => 'buy', 'productId' => 123]
    $productId = $payload['productId'] ?? null;

    // Обработка действия...

    return true; // Отмечаем событие как обработанное
});
```

### Методы CallbackJsonHandler

#### onAction

Регистрирует обработчик для конкретного действия.

**Параметры:**

| Параметр   | Тип                                           | Описание                                           |
|------------|-----------------------------------------------|----------------------------------------------------|
| `$name`    | `non-empty-string`                            | Имя действия (значение ключа `$actionKey` в JSON). |
| `$handler` | `Closure(MessageCallbackEvent): (bool\|void)` | Обработчик действия.                               |

**Возвращает:** `$this`

#### setActionMaxLength

Устанавливает максимальную длину имени действия.

**Параметры:**

| Параметр           | Тип            | Описание                                                          |
|--------------------|----------------|-------------------------------------------------------------------|
| `$actionMaxLength` | `positive-int` | Максимальная длина действия в символах UTF-8. По умолчанию: `64`. |

**Возвращает:** `$this`

#### getActionKey

Возвращает имя ключа, используемого для извлечения действия из JSON.

**Возвращает:** `non-empty-string`

#### getActionMaxLength

Возвращает текущую максимальную длину действия.

**Возвращает:** `positive-int`

## Особенности обработки Callback-событий

### Имя действия и полезная нагрузка

#### CallbackHandler

Класс `CallbackHandler` может работать как с полезной нагрузкой, так и без неё. Для активации возможности работы с
полезной нагрузкой необходимо установить в свойство `$callbackHandler->actionSeparator` строку-разделитель действия и
полезной нагрузки.

```php
$callbackHandler->setActionSeparator(':');

// При нажатии кнопки с payload "buy:product_123":
// - $event->userData['__action'] = 'buy'
// - $event->userData['__payload'] = 'product_123'
```

Если payload начинаетсяся с `{`, обработчик пропускает событие (предполагается, что это JSON и должен использоваться
`CallbackJsonHandler`).

#### CallbackJsonHandler

Класс `CallbackJsonHandler` позволяет работать с `payload` в JSON-формате. Данный класс ожидает в конструкторе имя
ключа, в котором будет храниться действие.

```php
$callbackHandler = new CallbackJsonHandler(actionKey: 'cmd');

// При нажатии кнопки с payload '{"cmd": "start", "data": "value"}':
// - $event->userData['__action'] = 'start'
// - $event->userData['__payload'] = ['cmd' => 'start', 'data' => 'value']
```

Если payload не является валидным JSON или не содержит указанный ключ, обработчик пропускает событие.

#### userData

Оба класса записывают в `userData` 2 значения:

| Ключ                            | Тип                   | Описание                                                                            |
|---------------------------------|-----------------------|-------------------------------------------------------------------------------------|
| `$event->userData['__action']`  | `string\|null`        | Имя извлечённого действия.                                                          |
| `$event->userData['__payload']` | `string\|array\|null` | Полезная нагрузка (строка для `CallbackHandler`, массив для `CallbackJsonHandler`). |

**Имя действия** по умолчанию ограничено **64** символами в кодировке UTF-8. Действия длиннее этого значения обработаны
не будут. Однако Вы можете изменить это значение, изменив параметр `$callbackHandler->actionMaxLength`.

### Статус события

При обработке событий важно изменить статус **события** на «обработано», если Вы не хотите, чтобы Ваше **событие** было
обработано другими обработчиками **событий**.

Больше о статусах **событий** Вы можете прочитать в разделе [Обработка событий](ProcessingEvents.md).

```php
$callbackHandler->onAction('buy', function (MessageCallbackEvent $event): bool {
    // Обработка действия...

    return true; // Отмечаем событие как обработанное
});
```

### Отправка ответа на Callback

После обработки callback-события Вы можете отправить ответ пользователю, используя методы события:

```php
$callbackHandler->onAction('buy', function (MessageCallbackEvent $event): bool {
    // Обработка покупки...

    // Отправка ответа с обновлением сообщения
    $event->answer('✅ Товар добавлен в корзину!');

    return true; // Отмечаем событие как обработанное
});
```

Вы также можете отправить только одноразовое уведомление, не изменяя сообщение:

```php
$callbackHandler->onAction('buy', function (MessageCallbackEvent $event): bool {
    // Обработка покупки...

    // Отправка только уведомления
    $event->answerNotification('✅ Товар добавлен в корзину!');

    return true; // Отмечаем событие как обработанное
});
```

Подробнее о методах ответа на Callback и статусах событий читайте в разделе [Обработка событий](ProcessingEvents.md).
