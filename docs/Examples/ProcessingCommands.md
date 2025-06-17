# Примеры обработки команд

## Введение

В этом документе представлены примеры обработки команд ботом MaxBot с использованием CommandHandler.

**Связанная документация:**

- [Обработка команд](../ProcessingCommands.md)
- [Обработка событий](../ProcessingEvents.md)
- [Обработка нажатий кнопок](../ProcessingCallbacks.md)
- [Примеры обработки событий](ProcessingEvents.md)
- [Примеры обработки нажатий кнопок](ProcessingCallbacks.md)

## Базовые примеры

### Обработка команды /start

Простой пример обработки команды запуска:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret');

$commandHandler = $bot->getCommandHandler();

$commandHandler->onCommand('start', function (MessageCreatedEvent $event): bool {
    $event->reply('Привет! Я тестовый бот. Отправьте /help для списка команд.');

    return true; // Отмечаем событие как обработанное
});
```

### Обработка нескольких команд

Регистрация обработчиков для разных команд:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$commandHandler = $bot->getCommandHandler();

// Команда /help
$commandHandler->onCommand('help', function (MessageCreatedEvent $event): bool {
    $helpText = "Доступные команды:\n"
              . "/start - Запустить бота\n"
              . "/help - Показать помощь\n"
              . "/info - Информация о боте\n"
              . "/settings - Настройки";
    
    $event->reply($helpText);

    return true;
});

// Команда /info
$commandHandler->onCommand('info', function (MessageCreatedEvent $event): bool {
    $botInfo = $event->apiClient->getMyInfo();
    
    $infoText = "Бот: {$botInfo->getName()}\n"
              . "ID: {$botInfo->getUserId()}";
    
    $event->reply($infoText);

    return true;
});
```

### Обработка неизвестных команд

Использование fallback-обработчика для команд без специфичного обработчика:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$commandHandler = $bot->getCommandHandler();

// Обработчик для всех неизвестных команд
$commandHandler->onCommands(function (MessageCreatedEvent $event): bool {
    $command = $event->userData['__command'];
    
    $event->reply("Команда /$command не найдена. Отправьте /help для списка команд.");

    return true;
});
```

## Работа с аргументами команд

### Использование разделителя команд

Настройка разделителя для получения аргументов:

```php
use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

$bot = new MaxBot('your-access-token', 'your-secret');

// Используем пробел как разделитель
$commandHandler = $bot->getCommandHandler(' ');

// Команда с аргументами: /echo Привет, мир!
$commandHandler->onCommand('echo', function (MessageCreatedEvent $event): bool {
    $payload = $event->userData['__payload'];
    
    if ($payload === null) {
        $event->reply('Использование: /echo <текст>');
    } else {
        $event->reply('Вы сказали: ' . $payload);
    }

    return true;
});
```

### Парсинг аргументов команды

Обработка команды с несколькими аргументами:

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

// Используем пробел как разделитель
$commandHandler = $bot->getCommandHandler(' ');

// Команда: /add 10 20
$commandHandler->onCommand('add', function (MessageCreatedEvent $event): bool {
    $payload = $event->userData['__payload'];
    
    if ($payload === null) {
        $event->reply('Использование: /add <число1> <число2>');
        
        return true;
    }

    $parts = explode(' ', $payload);

    if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
        $event->reply('Ошибка: укажите два числа через пробел');
        
        return true;
    }

    $result = (float)$parts[0] + (float)$parts[1];
    $event->reply("Результат: {$parts[0]} + {$parts[1]} = $result");

    return true;
});
```

## Команды с проверкой условий

### Проверка прав пользователя

```php
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;

// Список администраторов
$adminIds = [123456, 789012];

$commandHandler = $bot->getCommandHandler();

$commandHandler->onCommand('admin', function (MessageCreatedEvent $event) use ($adminIds): bool {
    $userId = $event->getMessage()->getSender()?->getUserId();
    
    if (!in_array($userId, $adminIds, true)) {
        $event->reply('У вас нет прав для выполнения этой команды');
        
        return true;
    }

    // Выполняем административное действие
    $event->reply('Административная команда выполнена');

    return true;
});
```

## Полный пример бота с командами

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MaxMessenger\Bot\MaxBot;
use MaxMessenger\Bot\MaxBot\Events\MessageCreatedEvent;
use MaxMessenger\Bot\MaxBot\Events\BaseEvent;
use Throwable;

$bot = new MaxBot('your-access-token', 'your-secret');

$commandHandler = $bot->getCommandHandler();
$commandHandler->setCommandSeparator(' ');

// Команда /start
$commandHandler->onCommand('start', function (MessageCreatedEvent $event): bool {
    $event->reply("Привет! Я бот с командами.\n\nОтправьте /help для списка команд.");

    return true;
});

// Команда /help
$commandHandler->onCommand('help', function (MessageCreatedEvent $event): bool {
    $help = "Доступные команды:\n\n"
          . "/start - Запустить бота\n"
          . "/help - Показать помощь\n"
          . "/echo <текст> - Повторить текст\n"
          . "/calc <a> <оп> <b> - Калькулятор\n"
          . "/info - Информация";

    $event->reply($help);

    return true;
});

// Команда /echo
$commandHandler->onCommand('echo', function (MessageCreatedEvent $event): bool {
    $payload = $event->userData['__payload'];
    
    if ($payload === null) {
        $event->reply('Использование: /echo <текст>');
    } else {
        $event->reply($payload);
    }
    
    return true;
});

// Команда /calc
$commandHandler->onCommand('calc', function (MessageCreatedEvent $event): bool {
    $payload = $event->userData['__payload'];

    if ($payload === null) {
        $event->reply('Использование: /calc 10 + 20');

        return true;
    }

    $parts = explode(' ', $payload, 3);

    if (count($parts) !== 3 || !is_numeric($parts[0]) || !is_numeric($parts[2])) {
        $event->reply('Ошибка: формат /calc 10 + 20');

        return true;
    }

    [$num1, $op, $num2] = $parts;

    $result = match($op) {
        '+' => $num1 + $num2,
        '-' => $num1 - $num2,
        '*' => $num1 * $num2,
        '/' => $num2 != 0 ? $num1 / $num2 : 'Ошибка: деление на ноль',
        default => 'Неизвестная операция'
    };

    $event->reply("$num1 $op $num2 = $result");

    return true;
});

// Команда /info
$commandHandler->onCommand('info', function (MessageCreatedEvent $event): bool {
    $botInfo = $event->apiClient->getMyInfo();

    $info = "Информация о боте:\n"
          . "Имя: {$botInfo->getName()}\n"
          . "ID: {$botInfo->getUserId()}";

    $event->reply($info);

    return true;
});

// Обработка неизвестных команд
$commandHandler->onCommands(function (MessageCreatedEvent $event): bool {
    $command = $event->userData['__command'];

    $event->reply("Команда /$command не найдена. Отправьте /help");

    return true;
});

// Обработка ошибок
$bot->onException(function (Throwable $exception, BaseEvent $event): bool {
    error_log("Ошибка: " . $exception->getMessage());

    return true;
});

// Запуск обработки обновлений
$bot->handleFromGlobal();
```
