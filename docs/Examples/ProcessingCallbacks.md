# Примеры обработки нажатий кнопок

## Введение

В этом документе представлены примеры обработки callback-событий (нажатий кнопок) с использованием CallbackHandler и
CallbackJsonHandler.

**Связанная документация:**

- [Обработка нажатий кнопок](../ProcessingCallbacks.md)
- [Обработка событий](../ProcessingEvents.md)
- [Обработка команд](../ProcessingCommands.md)
- [Примеры обработки событий](ProcessingEvents.md)
- [Примеры обработки команд](ProcessingCommands.md)

## Базовые примеры

### Простая обработка нажатия кнопки

Сначала отправим сообщение с кнопкой:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\BotStartedEvent;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$bot = new MaxBot('your-access-token', 'your-secret');

// Отправка сообщения с inline-кнопкой
$bot->onBotStarted(function (BotStartedEvent $event): bool {
    $message = new NewMessageBody('Выберите действие:');
    $message->addInlineKeyboard()
        ->addCallbackButton('❓ Помощь', 'help')
        ->newRow()
        ->addCallbackButton('⚙️ Настройки', 'settings');
    
    $event->sendToUser($message);

    return true; // Отмечаем событие как обработанное
});
```

Теперь обработаем нажатие кнопки с помощью CallbackHandler:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$callbackHandler = $bot->addCallbackHandler();

$callbackHandler->onAction('help', function (MessageCallbackEvent $event): bool {
    $event->answer('Раздел помощи. Выберите команду из меню.');

    return true; // Отмечаем событие как обработанное
});
```

### Обработка с аргументами

Использование разделителя для получения аргументов:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$callbackHandler = $bot->addCallbackHandler(actionSeparator: ':');

// Payload вида "buy:product_123"
$callbackHandler->onAction('buy', function (MessageCallbackEvent $event): bool {
    $action = $event->userData['__action'];   // 'buy'
    $productId = $event->userData['__payload']; // 'product_123'

    // Обработка покупки товара
    $event->reply("Товар $productId добавлен в корзину!", true);

    return true; // Отмечаем событие как обработанное
});
```

### Обработка нескольких действий

Регистрация обработчиков для разных действий:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$callbackHandler = $bot->addCallbackHandler();

// Действие /help
$callbackHandler->onAction('help', function (MessageCallbackEvent $event): bool {
    $event->answer('Выберите интересующий вас раздел.');

    return true; // Отмечаем событие как обработанное
});

// Действие /settings
$callbackHandler->onAction('settings', function (MessageCallbackEvent $event): bool {
    $event->answer('Настройки бота:\n- Уведомления: Вкл\n- Язык: Русский');

    return true; // Отмечаем событие как обработанное
});

// Действие /profile
$callbackHandler->onAction('profile', function (MessageCallbackEvent $event): bool {
    $user = $event->getUser();
    
    $profile = "Профиль пользователя:\n"
             . "Имя: {$user->getFirstName()}\n"
             . "ID: {$user->getUserId()}";
    
    $event->answer($profile);

    return true; // Отмечаем событие как обработанное
});
```

## Работа с CallbackJsonHandler

### Простая обработка JSON-payload

Использование CallbackJsonHandler для сложных данных:

```php
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$message = new NewMessageBody('Карточка товара');
$message->addInlineKeyboard()
    ->addCallbackButton('Добавить в корзину', {'action' => 'buy', 'productId' => 123, 'quantity' => 2});
```

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$callbackHandler = $bot->addCallbackJsonHandler(actionKey: 'action');

// Ожидается payload вида: {"action": "buy", "productId": 123, "quantity": 2}
$callbackHandler->onAction('buy', function (MessageCallbackEvent $event): bool {
    $action = $event->userData['__action'];  // 'buy'
    $payload = $event->userData['__payload']; // ['action' => 'buy', 'productId' => 123, 'quantity' => 2]
    
    $productId = $payload['productId'] ?? null;
    $quantity = $payload['quantity'] ?? 1;

    if ($productId === null) {
        $event->answerNotification('❌ Ошибка: не указан товар');
        
        return true; // Отмечаем событие как обработанное
    }

    // Обработка покупки
    $event->reply("✅ Товар $productId (кол-во: $quantity) добавлен в корзину!", true);

    return true; // Отмечаем событие как обработанное
});
```

### Обработка нескольких действий с JSON

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$callbackHandler = $bot->addCallbackJsonHandler(actionKey: 'cmd');

// Действие: {"cmd": "navigate", "page": "settings"}
$callbackHandler->onAction('navigate', function (MessageCallbackEvent $event): bool {
    $payload = $event->userData['__payload'];
    $page = $payload['page'] ?? 'home';
    
    $event->answer("Переход на страницу: $page");

    return true; // Отмечаем событие как обработанное
});

// Действие: {"cmd": "select", "item": "option_1"}
$callbackHandler->onAction('select', function (MessageCallbackEvent $event): bool {
    $payload = $event->userData['__payload'];
    $item = $payload['item'] ?? null;
    
    if ($item !== null) {
        $event->answer("Выбрано: $item");
    } else {
        $event->answerNotification('❌ Ничего не выбрано');
    }

    return true; // Отмечаем событие как обработанное
});
```

## Работа с ответами на Callback

### Обновление сообщения

Использование метода `answer()` для изменения сообщения:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;
use MaxMessenger\Bot\Models\Enums\TextFormat;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;

$callbackHandler = $bot->addCallbackHandler(actionSeparator: ':');

$callbackHandler->onAction('vote', function (MessageCallbackEvent $event): bool {
    $option = $event->userData['__payload'];
    
    $responseMessage = new NewMessageBody(
        text: "✅ Вы проголосовали за: **$option**",
        format: TextFormat::Markdown
    );
    
    $event->answer($responseMessage);

    return true; // Отмечаем событие как обработанное
});
```

### Отправка только уведомления

Использование `answerNotification()` для одноразового уведомления:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$callbackHandler = $bot->addCallbackHandler(actionSeparator: ':');

$callbackHandler->onAction('download', function (MessageCallbackEvent $event): bool {
    $fileId = $event->userData['__payload'];
    
    // Начинаем загрузку файла...
    
    // Отправляем только уведомление (не изменяя исходное сообщение)
    $event->answerNotification('📥 Загрузка начата...');

    return true; // Отмечаем событие как обработанное
});
```

### Удаление сообщения после нажатия

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;

$callbackHandler = $bot->addCallbackHandler();

$callbackHandler->onAction('dismiss', function (MessageCallbackEvent $event): bool {
    // Удаляем сообщение с кнопкой
    $event->deleteMessage();

    return true; // Отмечаем событие как обработанное
});
```

## Полный пример бота с callback-кнопками

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\BaseEvent;
use MaxMessenger\Bot\MaxBot\Events\BotStartedEvent;
use MaxMessenger\Bot\MaxBot\Events\MessageCallbackEvent;
use MaxMessenger\Bot\Models\Enums\TextFormat;
use MaxMessenger\Bot\Models\Requests\NewMessageBody;
use Throwable;

$bot = new MaxBot('your-access-token', 'your-secret');

// Обработка /start
$bot->onBotStarted(function (BotStartedEvent $event): bool {
    $message = new NewMessageBody('Привет! Я бот с интерактивными кнопками.\n\nВыберите действие:');
    $message->addInlineKeyboard()
        ->addCallbackButton('📊 Статистика', 'stats')
        ->addCallbackButton('⚙️ Настройки', 'settings')
        ->newRow()
        ->addCallbackButton('❓ Помощь', 'help');
    
    $event->sendToUser($message);

    return true; // Отмечаем событие как обработанное
});

// CallbackHandler для обработки нажатий кнопок
$callbackHandler = $bot->addCallbackHandler(actionSeparator: ':');

// Действие: stats
$callbackHandler->onAction('stats', function (MessageCallbackEvent $event): bool {
    $statsMessage = new NewMessageBody(
        text: "📊 **Статистика бота:**\n\n"
            . "• Пользователей: 1,234\n"
            . "• Сообщений сегодня: 567\n"
            . "• Аптайм: 99.9%",
        format: TextFormat::Markdown
    );
    $statsMessage->addInlineKeyboard()
        ->addCallbackButton('🔄 Обновить', 'stats')
        ->addCallbackButton('🏠 Назад', 'home');
    
    $event->answer($statsMessage);

    return true; // Отмечаем событие как обработанное
});

// Действие: settings
$callbackHandler->onAction('settings', function (MessageCallbackEvent $event): bool {
    $settingsMessage = new NewMessageBody(
        text: "⚙️ **Настройки:**\n\n"
            . "• Уведомления: ✅ Вкл\n"
            . "• Язык: 🇷🇺 Русский\n"
            . "• Тема: 🌙 Тёмная",
        format: TextFormat::Markdown
    );
    $settingsMessage->addInlineKeyboard()
        ->addCallbackButton('🔔 Уведомления', 'toggle_notifications')
        ->addCallbackButton('🏠 Назад', 'home');
    
    $event->answer($settingsMessage);

    return true; // Отмечаем событие как обработанное
});

// Действие: help
$callbackHandler->onAction('help', function (MessageCallbackEvent $event): bool {
    $helpMessage = new NewMessageBody(
        text: "❓ **Помощь:**\n\n"
            . "Используйте кнопки ниже для навигации:\n\n"
            . "• **Статистика** — просмотр статистики бота\n"
            . "• **Настройки** — изменение параметров\n"
            . "• **Помощь** — это сообщение",
        format: TextFormat::Markdown
    );
    
    $event->answer($helpMessage);

    return true; // Отмечаем событие как обработанное
});

// Действие: home (возврат в главное меню)
$callbackHandler->onAction('home', function (MessageCallbackEvent $event): bool {
    $homeMessage = new NewMessageBody('🏠 **Главное меню:**\n\nВыберите действие:', format: TextFormat::Markdown);
    $homeMessage->addInlineKeyboard()
        ->addCallbackButton('📊 Статистика', 'stats')
        ->addCallbackButton('⚙️ Настройки', 'settings')
        ->newRow()
        ->addCallbackButton('❓ Помощь', 'help');
    
    $event->answer($homeMessage);

    return true; // Отмечаем событие как обработанное
});

// Действие: toggle_notifications
$callbackHandler->onAction('toggle_notifications', function (MessageCallbackEvent $event): bool {
    // Переключаем уведомления (здесь можно добавить реальную логику)
    
    $event->answerNotification('🔔 Уведомления переключены');

    return true; // Отмечаем событие как обработанное
});

// Обработка ошибок
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    error_log("Ошибка при обработке callback: " . $exception->getMessage());

    return true; // Отмечаем событие как обработанное, чтобы не прерывать работу
});

// Запуск обработки обновлений
$bot->handleFromGlobal();
```
