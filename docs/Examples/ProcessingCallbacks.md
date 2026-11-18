# Примеры обработки нажатий кнопок

## Введение

В этом документе представлены примеры обработки нажатий кнопок с использованием `CallbackHandler`
и `CallbackJsonHandler`.
Обработчики можно зарегистрировать через методы `MaxBot::onCallback()` и `MaxBot::onCallbackJson()`.
Для настройки основных обработчиков используются `MaxBot::getCallbackHandler()` и `MaxBot::getCallbackJsonHandler()`.

Связанная документация:

- [Обработка нажатий кнопок](../ProcessingCallbacks.md)
- [Обработка событий бота](../ProcessingEvents.md)
- [Обработка команд](../ProcessingCommands.md)
- [Примеры обработки событий бота](ProcessingEvents.md)
- [Примеры обработки команд](ProcessingCommands.md)

## Базовые примеры

### Обработка действия `help`

Пример отправки сообщения с callback-кнопками:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Event\BotStartedEvent;
use MaxMessenger\Bot\Model\Request\NewMessageBody;

$bot = new MaxBot('your-access-token', 'your-secret', useRussianTrustedCaCertificates: true);

// Отправка сообщения с inline-клавиатурой
$bot->onBotStarted(static function (BotStartedEvent $event): void {
    $message = new NewMessageBody('Выберите действие:');
    $message
        ->addInlineKeyboard()
        ->addCallbackButton('❓ Помощь', 'help')
        ->newRow()
        ->addCallbackButton('⚙️ Настройки', 'settings');

    $event->sendMessageToUser($message);
});
```

Пример регистрации обработчика действия `help`:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;
use MaxMessenger\Bot\Model\Request\NewMessageBody;

$bot->onCallback('help', static function (MessageCallbackEvent $event, string $action, ?string $payload): void {
    $message = new NewMessageBody('Раздел помощи. Выберите команду из меню:');
    $message
        ->addInlineKeyboard()
        ->addCallbackButton('📖 Список команд', 'commands')
        ->newRow()
        ->addCallbackButton('✉️ Связаться с поддержкой', 'support');

    $event->answer($message);
});
```

### Обработка нескольких действий

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

// Действие help
$bot->onCallback('help', static function (MessageCallbackEvent $event, string $action, ?string $payload): void {
    $event->answer('Выберите интересующий вас раздел.');
});

// Действие settings
$bot->onCallback('settings', static function (MessageCallbackEvent $event, string $action, ?string $payload): void {
    $event->answer("Настройки бота:\n- Уведомления: Вкл\n- Язык: Русский");
});

// Действие profile
$bot->onCallback('profile', static function (MessageCallbackEvent $event, string $action, ?string $payload): void {
    $user = $event->getUser();

    $profile = "Профиль пользователя:\n"
        . "Имя: {$user->getFirstName()}\n"
        . "ID: {$user->getUserId()}";

    $event->answer($profile);
});
```

## Работа с аргументами

### Использование разделителя

В примере двоеточие используется как разделитель имени действия и payload:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

$bot->getCallbackHandler()->setActionSeparator(':');

// Payload вида "buy:product_123"
$bot->onCallback('buy', static function (MessageCallbackEvent $event, string $action, ?string $payload): void {
    $productId = $payload; // 'product_123'

    // Обработка покупки товара
    $event->reply("Товар $productId добавлен в корзину!", true);
});
```

## Работа с JSON-payload

### Обработка действия с JSON-payload

Пример создания кнопки с JSON-payload:

```php
use MaxMessenger\Bot\Model\Request\NewMessageBody;

$message = new NewMessageBody('Карточка товара');
$message
    ->addInlineKeyboard()
    ->addCallbackButton('Добавить в корзину', ['action' => 'buy', 'productId' => 123, 'quantity' => 2]);
```

Пример регистрации обработчика действия `buy`:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

// Ожидается payload вида: {"action": "buy", "productId": 123, "quantity": 2}
$bot->onCallbackJson('buy', static function (MessageCallbackEvent $event, string $action, array $payload): void {
    $productId = $payload['productId'] ?? null;
    $quantity = $payload['quantity'] ?? 1;

    if ($productId === null) {
        $event->answer('❌ Ошибка: не указан товар');

        return;
    }

    // Обработка покупки
    $event->reply("✅ Товар $productId (кол-во: $quantity) добавлен в корзину!", true);
});
```

### Обработка нескольких действий

В примере имя действия хранится в JSON-поле `cmd`:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

$bot->getCallbackJsonHandler()->setActionKey('cmd');

// Действие {"cmd": "navigate", "page": "settings"}
$bot->onCallbackJson('navigate', static function (MessageCallbackEvent $event, string $action, array $payload): void {
    $page = $payload['page'] ?? 'home';

    $event->answer("Переход на страницу: $page");
});

// Действие {"cmd": "select", "item": "option_1"}
$bot->onCallbackJson('select', static function (MessageCallbackEvent $event, string $action, array $payload): void {
    $item = $payload['item'] ?? null;

    if ($item !== null) {
        $event->answer("Выбрано: $item");
    } else {
        $event->answer('❌ Ничего не выбрано');
    }
});
```

## Удаление сообщения

Пример удаления сообщения через `MessageCallbackEvent::deleteMessage()`:

```php
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

$bot->onCallback('dismiss', static function (MessageCallbackEvent $event, string $action, ?string $payload): void {
    // Удаляем сообщение с кнопкой
    $event->deleteMessage();
});
```

## Отдельный обработчик

В примере отдельный `CallbackHandler` зарегистрирован через `onMessageCallback()`:

```php
use MaxMessenger\Bot\MaxBot\CallbackHandler;
use MaxMessenger\Bot\MaxBot\Event\MessageCallbackEvent;

$callbackHandler = new CallbackHandler(actionSeparator: ':');
$bot->onMessageCallback($callbackHandler->handle(...));

$callbackHandler->onAction('buy', static function (
    MessageCallbackEvent $event,
    string $action,
    ?string $payload,
): void {
    $event->answer('Товар добавлен в корзину!');
});
```

Отдельный обработчик JSON-действий `CallbackJsonHandler` регистрируется таким же способом.
